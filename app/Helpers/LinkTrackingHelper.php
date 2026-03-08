<?php

namespace App\Helpers;

class LinkTrackingHelper
{
    /**
     * Upraví HTML obsah článku a přidá tracking ke všem externím odkazům
     */
    public static function addTrackingToLinks($html, $articleId)
    {
        if (empty($html) || !$articleId) {
            return $html;
        }

        // Uložíme originální HTML pro fallback
        $originalHtml = $html;

        try {
            // Použijeme DOMDocument pro lepší manipulaci s HTML
            $dom = new \DOMDocument();
            
            // Potlačíme chyby při parsování HTML
            libxml_use_internal_errors(true);
            
            // Načteme HTML s UTF-8 encoding
            // Přidáme wrapper pro správné parsování a detekci kódování
            // Použijeme mb_convert_encoding pouze pokud je k dispozici
            if (function_exists('mb_convert_encoding')) {
                $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
            }
            
            // Přidáme meta tag pro vynucení UTF-8, aby DOMDocument neblbnul s češtinou
            $wrappedHtml = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>';
            
            // Načtení HTML s ošetřením chyb
            $loaded = @$dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            if (!$loaded) {
                throw new \Exception("Failed to load HTML into DOMDocument");
            }
            
            // Vymažeme chyby libxml
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $allLinks = $xpath->query('//a[@href]');
            
            if ($allLinks === false) {
                 return $originalHtml; // Pokud XPath selže, vrátíme originál
            }

            // Najdeme všechny externí odkazy a jejich pozice
            $externalLinks = [];
            $linkIndex = 0;
        
        foreach ($allLinks as $link) {
            $href = $link->getAttribute('href');
            
            // Přeskočíme interní odkazy (začínající / nebo #)
            if (empty($href) || $href[0] === '/' || $href[0] === '#') {
                continue;
            }
            
            // Přeskočíme relativní cesty bez protokolu
            if (!preg_match('/^https?:\/\//', $href)) {
                continue;
            }
            
            $externalLinks[] = [
                'element' => $link,
                'index' => $linkIndex++,
                'href' => $href
            ];
        }
        
        $totalExternalLinks = count($externalLinks);
        
        // Určíme typ odkazu
        $linkTypes = [
            'social' => ['facebook.com', 'instagram.com', 'twitter.com', 'x.com', 'linkedin.com', 'youtube.com', 'tiktok.com', 'pinterest.com'],
            'shop' => ['eshop', 'shop', 'obchod', 'koupit', 'buy', 'cart'],
            'internal' => [] // interní odkazy už filtrujeme výše
        ];

        foreach ($externalLinks as $linkData) {
            $link = $linkData['element'];
            $href = $linkData['href'];
            $index = $linkData['index'];
            
            // Určíme pozici odkazu
            $position = 'middle';
            if ($totalExternalLinks === 1) {
                $position = 'only';
            } elseif ($index === 0) {
                $position = 'first';
            } elseif ($index === $totalExternalLinks - 1) {
                $position = 'last';
            } elseif ($index < $totalExternalLinks / 3) {
                $position = 'top';
            } elseif ($index > ($totalExternalLinks * 2) / 3) {
                $position = 'bottom';
            }
            
            // Určíme typ odkazu
            $linkType = 'external';
            $hrefLower = strtolower($href);
            foreach ($linkTypes['social'] as $social) {
                if (strpos($hrefLower, $social) !== false) {
                    $linkType = 'social';
                    break;
                }
            }
            if ($linkType === 'external') {
                foreach ($linkTypes['shop'] as $shop) {
                    if (strpos($hrefLower, $shop) !== false) {
                        $linkType = 'shop';
                        break;
                    }
                }
            }

            // Vytvoříme tracking token
            $linkText = trim($link->textContent);
            $token = base64_encode(json_encode([
                'article_id' => $articleId,
                'url' => $href,
                'link_text' => $linkText,
                'link_position' => $position,
                'link_type' => $linkType,
                'link_index' => $index
            ]));
            
            // URL-safe encoding
            $token = rtrim(strtr($token, '+/', '-_'), '=');
            
            // Nastavíme nový href s tracking
            $link->setAttribute('href', '/track/' . $token);
            
            // Přidáme data atributy pro JavaScript tracking
            $link->setAttribute('data-track-token', $token);
            $link->setAttribute('data-link-position', $position);
            
            // Přidáme target="_blank" pro externí odkazy, pokud ještě není
            if (!$link->hasAttribute('target')) {
                $link->setAttribute('target', '_blank');
            }
            
            // Přidáme rel="noopener noreferrer" pro bezpečnost
            if (!$link->hasAttribute('rel')) {
                $link->setAttribute('rel', 'noopener noreferrer');
            }
        }

            // Vrátíme upravené HTML
            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body) {
                $newHtml = '';
                foreach ($body->childNodes as $node) {
                    $newHtml .= $dom->saveHTML($node);
                }
                return $newHtml;
            }
            
            return $originalHtml;

        } catch (\Exception $e) {
            // Logování chyby (volitelné, pokud existuje logger)
            error_log("LinkTrackingHelper Error: " . $e->getMessage());
            // V případě jakékoliv chyby vrátíme původní HTML, aby se obsah zobrazil
            return $originalHtml;
        }
    }
}

