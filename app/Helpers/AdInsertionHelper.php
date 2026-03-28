<?php

namespace App\Helpers;

class AdInsertionHelper
{
    public static function countContentBlocks(string $html): int
    {
        $blockTags = [
            'p' => true,
            'figure' => true,
            'img' => true,
            'blockquote' => true,
            'iframe' => true,
            'h2' => true,
            'h3' => true,
            'ul' => true,
            'ol' => true,
            'div' => true,
        ];

        $wrapped = '<div id="ad-wrap">' . $html . '</div>';
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previousSetting = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        $wrapper = $doc->getElementById('ad-wrap');
        if (!$wrapper) {
            return 0;
        }

        $blockCount = 0;
        foreach (iterator_to_array($wrapper->childNodes) as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($node->nodeName);
            if (!isset($blockTags[$tag])) {
                continue;
            }

            if ($tag === 'div') {
                $class = '';
                if ($node->attributes) {
                    $classAttr = $node->attributes->getNamedItem('class');
                    if ($classAttr) {
                        $class = (string) $classAttr->nodeValue;
                    }
                }
                if ($class === '') {
                    continue;
                }
            }

            $blockCount++;
        }

        return $blockCount;
    }

    public static function getInsertAfterBlocksForLength(int $blockCount, int $maxAds = 2): array
    {
        if ($blockCount <= 0) {
            return [];
        }

        $adsCount = 1;
        if ($maxAds >= 2 && $blockCount >= 10) {
            $adsCount = 2;
        }

        $insertAfter = [];
        if ($adsCount === 1) {
            if ($blockCount === 1) {
                $insertAfter[] = 1;
            } else {
                $pos = (int) round($blockCount * 0.35);
                $pos = max(2, min($blockCount, $pos));
                $insertAfter[] = $pos;
            }
            return $insertAfter;
        }

        $pos1 = (int) round($blockCount * 0.28);
        $pos1 = max(2, min($blockCount - 3, $pos1));

        $pos2 = (int) round($blockCount * 0.62);
        $pos2 = max($pos1 + 3, min($blockCount, $pos2));

        $insertAfter[] = $pos1;
        $insertAfter[] = $pos2;
        return $insertAfter;
    }

    public static function insertAdsIntoHtml(string $html, array $ads, array $afterBlocks): string
    {
        $ads = array_values(array_filter($ads, function ($ad) {
            return is_array($ad) && !empty($ad['obrazek']);
        }));

        if (trim($html) === '' || empty($ads) || empty($afterBlocks)) {
            return $html;
        }

        $wrapped = '<div id="ad-wrap">' . $html . '</div>';

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previousSetting = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        $wrapper = $doc->getElementById('ad-wrap');
        if (!$wrapper) {
            return $html;
        }

        $blockTags = [
            'p' => true,
            'figure' => true,
            'img' => true,
            'blockquote' => true,
            'iframe' => true,
            'h2' => true,
            'h3' => true,
            'ul' => true,
            'ol' => true,
            'div' => true,
        ];

        $targets = [];
        foreach ($afterBlocks as $after) {
            $after = (int) $after;
            if ($after >= 1) {
                $targets[$after] = true;
            }
        }
        $targets = array_keys($targets);
        sort($targets);

        if (empty($targets)) {
            return $html;
        }

        $insertions = array_slice($targets, 0, max(1, count($ads)));

        $adIndex = 0;
        $currentBlock = 0;
        $insertLookup = array_fill_keys($insertions, true);

        foreach (iterator_to_array($wrapper->childNodes) as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($node->nodeName);
            if (!isset($blockTags[$tag])) {
                continue;
            }

            if ($tag === 'div') {
                $class = '';
                if ($node->attributes) {
                    $classAttr = $node->attributes->getNamedItem('class');
                    if ($classAttr) {
                        $class = (string) $classAttr->nodeValue;
                    }
                }
                if ($class === '') {
                    continue;
                }
            }

            $currentBlock++;
            if (!isset($insertLookup[$currentBlock])) {
                continue;
            }

            $ad = $ads[$adIndex] ?? $ads[0];
            $adIndex++;

            $adNode = self::buildAdNode($doc, $ad);
            if ($adNode) {
                if ($node->nextSibling) {
                    $wrapper->insertBefore($adNode, $node->nextSibling);
                } else {
                    $wrapper->appendChild($adNode);
                }
            }
        }

        $output = '';
        foreach ($wrapper->childNodes as $child) {
            $output .= $doc->saveHTML($child);
        }
        return $output;
    }

    private static function buildAdNode(\DOMDocument $doc, array $ad): ?\DOMElement
    {
        $image = (string) ($ad['obrazek'] ?? '');
        if ($image === '') {
            return null;
        }

        $href = (string) ($ad['odkaz'] ?? '');
        $title = (string) ($ad['nazev'] ?? 'Reklama');

        $container = $doc->createElement('div');
        $container->setAttribute('class', 'article-ad');

        $img = $doc->createElement('img');
        $img->setAttribute('src', '/uploads/ads/' . ltrim($image, '/'));
        $img->setAttribute('alt', $title);
        $img->setAttribute('loading', 'lazy');
        $img->setAttribute('decoding', 'async');

        if ($href !== '') {
            $a = $doc->createElement('a');
            $a->setAttribute('href', $href);
            $a->setAttribute('target', '_blank');
            $a->setAttribute('rel', 'noopener noreferrer sponsored');
            $a->appendChild($img);
            $container->appendChild($a);
        } else {
            $container->appendChild($img);
        }

        return $container;
    }
}
