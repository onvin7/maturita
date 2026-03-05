<?php

namespace App\Helpers;

class SEOHelper
{
    private static $seoConfig = null;
    
    /**
     * Načte SEO konfiguraci ze souboru
     */
    public static function getConfig()
    {
        if (self::$seoConfig === null) {
            $configPath = __DIR__ . '/../../web/config/seo_config.json';
            if (file_exists($configPath)) {
                try {
                    $configContent = file_get_contents($configPath);
                    $config = json_decode($configContent, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        error_log('SEO Config JSON Error: ' . json_last_error_msg());
                        self::$seoConfig = self::getDefaultConfig();
                    } else {
                        self::$seoConfig = $config ?: self::getDefaultConfig();
                    }
                } catch (Exception $e) {
                    error_log('SEO Config Error: ' . $e->getMessage());
                    self::$seoConfig = self::getDefaultConfig();
                }
            } else {
                self::$seoConfig = self::getDefaultConfig();
            }
        }
        return self::$seoConfig;
    }
    
    /**
     * Výchozí SEO konfigurace
     */
    private static function getDefaultConfig()
    {
        return [
            'site' => [
                'name' => 'Cyklistický magazín',
                'url' => 'https://www.cyklistickey.cz',
                'description' => 'Sledujte nejnovější zprávy, tréninkové tipy, technické novinky a rozhovory ze světa cyklistiky.',
                'keywords' => 'cyklistika, kolo, závody, trénink, technika, novinky, magazín',
                'author' => 'Cyklistický magazín',
                'theme_color' => '#f1008d',
                'background_color' => '#ffffff'
            ],
            'social' => [
                'facebook' => 'https://www.facebook.com/profile.php?id=100094700727442',
                'instagram' => 'https://www.instagram.com/cyklistickey/',
                'youtube' => 'https://www.youtube.com/@cyklistickey',
                'tiktok' => 'https://www.tiktok.com/@cyklistickey'
            ],
            'contact' => [
                'phone' => '+420 608 644 786',
                'email' => 'jsem@cyklistickey.cz',
                'address' => 'Česká republika'
            ],
            'defaults' => [
                'title_suffix' => ' | Cyklistický magazín',
                'description_length' => 160,
                'keywords_length' => 10
            ]
        ];
    }
    
    /**
     * Generuje title tag s AI optimalizací
     */
    public static function generateTitle($title = null, $suffix = null, $keywords = [])
    {
        $config = self::getConfig();
        $suffix = $suffix ?? $config['defaults']['title_suffix'];
        
        if (empty($title)) {
            return $config['site']['name'];
        }
        
        // Použij AI optimalizaci pokud je dostupná
        if (class_exists('App\Helpers\AISEOHelper') && !empty($keywords)) {
            $optimizedTitle = \App\Helpers\AISEOHelper::generateOptimizedTitle($title, $keywords);
            return $optimizedTitle . $suffix;
        }
        
        return $title . $suffix;
    }
    
    /**
     * Generuje meta description s AI optimalizací
     */
    public static function generateDescription($content = null, $custom = null, $keywords = [])
    {
        $config = self::getConfig();
        
        if ($custom) {
            // Použij AI optimalizaci pokud je dostupná
            if (class_exists('App\Helpers\AISEOHelper') && !empty($keywords)) {
                return \App\Helpers\AISEOHelper::generateOptimizedDescription($custom, $keywords);
            }
            return self::truncateText($custom, $config['defaults']['description_length']);
        }
        
        if ($content) {
            $cleanContent = strip_tags($content);
            $cleanContent = preg_replace('/\s+/', ' ', $cleanContent);
            
            // Použij AI optimalizaci pokud je dostupná
            if (class_exists('App\Helpers\AISEOHelper') && !empty($keywords)) {
                return \App\Helpers\AISEOHelper::generateOptimizedDescription($cleanContent, $keywords);
            }
            
            return self::truncateText($cleanContent, $config['defaults']['description_length']);
        }
        
        return $config['site']['description'];
    }
    
    /**
     * Generuje keywords meta tag
     */
    public static function generateKeywords($content = null, $custom = [])
    {
        $config = self::getConfig();
        $keywords = array_merge(explode(', ', $config['site']['keywords']), $custom);
        
        if ($content) {
            // Extrahuj klíčová slova z obsahu
            $contentKeywords = self::extractKeywords($content);
            $keywords = array_merge($keywords, $contentKeywords);
        }
        
        // Odstraň duplicity a omezení na počet
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, $config['defaults']['keywords_length']);
        
        return implode(', ', $keywords);
    }
    
    /**
     * Extrahuje klíčová slova z obsahu pomocí AI
     */
    public static function extractKeywords($content, $limit = 5)
    {
        // Použij AI helper pro lepší extrakci
        if (class_exists('App\Helpers\AISEOHelper')) {
            return \App\Helpers\AISEOHelper::extractKeywords($content, $limit);
        }
        
        // Fallback na původní metodu
        $content = strip_tags($content);
        $content = strtolower($content);
        
        $stopWords = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'will', 'with', 'se', 'na', 'je', 'v', 'z', 'do', 'od', 'pro', 'k', 'o', 'u', 'za', 'po', 'při', 'mezi', 'když', 'kde', 'jak', 'co', 'který', 'která', 'které'];
        
        $words = preg_split('/\s+/', $content);
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        $wordCount = array_count_values($words);
        arsort($wordCount);
        
        return array_slice(array_keys($wordCount), 0, $limit);
    }
    
    /**
     * Generuje canonical URL
     */
    public static function generateCanonicalUrl($path = '')
    {
        $config = self::getConfig();
        return rtrim($config['site']['url'], '/') . '/' . ltrim($path, '/');
    }
    
    /**
     * Generuje hreflang data pro mezinárodní verze
     */
    public static function generateHreflangData($canonicalUrl)
    {
        $config = self::getConfig();
        
        if (!isset($config['international'])) {
            return [
                'cs' => $canonicalUrl,
                'x-default' => $canonicalUrl
            ];
        }
        
        $hreflangData = [];
        $baseUrl = $config['site']['url'];
        
        foreach ($config['international']['supported_languages'] as $lang) {
            if ($lang === $config['international']['default_language']) {
                $hreflangData[$lang] = $canonicalUrl;
            } else {
                $langUrl = $config['international']['language_urls'][$lang] ?? $baseUrl . '/' . $lang;
                $hreflangData[$lang] = str_replace($baseUrl, $langUrl, $canonicalUrl);
            }
        }
        
        $hreflangData['x-default'] = $canonicalUrl;
        
        return $hreflangData;
    }
    
    /**
     * Generuje Open Graph data
     */
    public static function generateOpenGraph($title, $description, $image = null, $type = 'website', $url = null)
    {
        $config = self::getConfig();
        
        return [
            'og:title' => $title,
            'og:description' => $description,
            'og:type' => $type,
            'og:url' => $url ?? $config['site']['url'],
            'og:image' => $image ?? $config['site']['url'] . '/assets/graphics/logo_text_cyklistickey.png',
            'og:site_name' => $config['site']['name'],
            'og:locale' => 'cs_CZ'
        ];
    }
    
    /**
     * Generuje Twitter Card data
     */
    public static function generateTwitterCard($title, $description, $image = null)
    {
        $config = self::getConfig();
        
        return [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $title,
            'twitter:description' => $description,
            'twitter:image' => $image ?? $config['site']['url'] . '/assets/graphics/logo_text_cyklistickey.png',
            'twitter:site' => '@cyklistickey',
            'twitter:creator' => '@cyklistickey'
        ];
    }
    
    /**
     * Generuje structured data pro článek
     */
    public static function generateArticleSchema($article, $author = null)
    {
        $config = self::getConfig();
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['nazev'] ?? '',
            'description' => self::generateDescription($article['obsah'] ?? ''),
            'image' => isset($article['nahled_foto']) ? $config['site']['url'] . '/' . $article['nahled_foto'] : null,
            'datePublished' => isset($article['datum']) ? date('c', strtotime($article['datum'])) : date('c'),
            'dateModified' => isset($article['updated_at']) ? date('c', strtotime($article['updated_at'])) : date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => ($author['name'] ?? '') . ' ' . ($author['surname'] ?? ''),
                'url' => $config['site']['url'] . '/author/' . ($author['name'] ?? '') . '-' . ($author['surname'] ?? '')
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $config['site']['name'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $config['site']['url'] . '/assets/graphics/logo_text_cyklistickey.png'
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => self::generateCanonicalUrl('article/' . ($article['url'] ?? ''))
            ]
        ];
        
        // Přidej kategorie pokud existují
        if (isset($article['kategorie']) && is_array($article['kategorie'])) {
            $schema['articleSection'] = array_map(function($cat) {
                return $cat['nazev_kategorie'] ?? '';
            }, $article['kategorie']);
        }
        
        return $schema;
    }
    
    /**
     * Generuje breadcrumbs structured data
     */
    public static function generateBreadcrumbSchema($breadcrumbs)
    {
        $config = self::getConfig();
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $config['site']['url'] . $breadcrumb['url']
            ];
        }
        
        return $schema;
    }
    
    /**
     * Generuje FAQ structured data
     */
    public static function generateFAQSchema($faqs)
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => []
        ];
        
        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }
        
        return $schema;
    }
    
    /**
     * Generuje breadcrumbs HTML s validací
     */
    public static function generateBreadcrumbsHTML($breadcrumbs)
    {
        if (empty($breadcrumbs) || !is_array($breadcrumbs)) {
            return '';
        }
        
        $html = '<nav class="breadcrumbs">';
        $html .= '<ol class="breadcrumb-list">';
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            // Validace breadcrumb struktury
            if (!isset($breadcrumb['name']) || !isset($breadcrumb['url'])) {
                continue;
            }
            
            $isLast = $index === count($breadcrumbs) - 1;
            $name = htmlspecialchars($breadcrumb['name'], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($breadcrumb['url'], ENT_QUOTES, 'UTF-8');
            
            $html .= '<li class="breadcrumb-item' . ($isLast ? ' active' : '') . '">';
            
            if ($isLast) {
                $html .= '<span aria-current="page">' . $name . '</span>';
            } else {
                $html .= '<a href="' . $url . '">' . $name . '</a>';
            }
            
            $html .= '</li>';
            
            if (!$isLast) {
                $html .= '<li class="breadcrumb-separator" aria-hidden="true">›</li>';
            }
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Zkracuje text na požadovanou délku
     */
    private static function truncateText($text, $length)
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        $lastSpace = strrpos($truncated, ' ');
        
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . '...';
    }
    
    /**
     * Kontroluje, zda je stránka admin stránka
     */
    public static function isAdminPage()
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($currentPath, '/admin') === 0;
    }
    
    /**
     * Generuje robots meta tag
     */
    public static function generateRobotsMeta()
    {
        if (self::isAdminPage()) {
            return 'noindex, nofollow, noarchive, nosnippet';
        }
        
        return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    }
    
    /**
     * Generuje alt text pro obrázek
     */
    public static function generateImageAlt($imagePath, $context = '')
    {
        $filename = basename($imagePath);
        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        
        // Pokud je v kontextu nějaký text, použij ho
        if (!empty($context)) {
            $context = strip_tags($context);
            $context = preg_replace('/\s+/', ' ', $context);
            $context = trim($context);
            
            if (strlen($context) > 100) {
                $context = substr($context, 0, 100) . '...';
            }
            
            return $context;
        }
        
        // Jinak generuj na základě názvu souboru
        $altText = str_replace(['_', '-'], ' ', $nameWithoutExt);
        $altText = ucwords($altText);
        
        return $altText;
    }
    
    /**
     * Generuje title pro obrázek
     */
    public static function generateImageTitle($imagePath, $context = '')
    {
        return self::generateImageAlt($imagePath, $context);
    }
    
    /**
     * Generuje optimalizované img tag
     */
    public static function generateImageTag($src, $alt = '', $title = '', $class = '', $lazy = true)
    {
        $alt = $alt ?: self::generateImageAlt($src);
        $title = $title ?: self::generateImageTitle($src);
        
        $lazyAttr = $lazy ? ' loading="lazy"' : '';
        $classAttr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
        
        return sprintf(
            '<img src="%s" alt="%s" title="%s"%s%s>',
            htmlspecialchars($src),
            htmlspecialchars($alt),
            htmlspecialchars($title),
            $lazyAttr,
            $classAttr
        );
    }
    
    /**
     * Generuje sitemap data s optimalizovanými dotazy
     */
    public static function generateSitemapData($db)
    {
        $config = self::getConfig();
        $sitemap = [];
        
        // Hlavní stránka
        $sitemap[] = [
            'url' => $config['site']['url'],
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];
        
        // Statické stránky
        $staticPages = [
            '/categories' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/authors' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/events' => ['changefreq' => 'weekly', 'priority' => '0.8'],
            '/appka' => ['changefreq' => 'monthly', 'priority' => '0.6'],
            '/kontakt' => ['changefreq' => 'monthly', 'priority' => '0.5']
        ];
        
        foreach ($staticPages as $path => $settings) {
            $sitemap[] = [
                'url' => $config['site']['url'] . $path,
                'lastmod' => date('Y-m-d'),
                'changefreq' => $settings['changefreq'],
                'priority' => $settings['priority']
            ];
        }
        
        // Optimalizovaný dotaz pro články - pouze potřebné sloupce
        try {
            $stmt = $db->query("
                SELECT url, datum, updated_at 
                FROM clanky 
                WHERE viditelnost = 1 
                AND datum <= NOW() 
                ORDER BY datum DESC 
                LIMIT 1000
            ");
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($articles as $article) {
                $lastmod = $article['updated_at'] ?? $article['datum'];
                $sitemap[] = [
                    'url' => $config['site']['url'] . '/article/' . $article['url'],
                    'lastmod' => date('Y-m-d', strtotime($lastmod)),
                    'changefreq' => 'weekly',
                    'priority' => '0.9'
                ];
            }
        } catch (\Exception $e) {
            error_log('Sitemap Articles Error: ' . $e->getMessage());
        }
        
        // Optimalizovaný dotaz pro kategorie
        try {
            $stmt = $db->query("SELECT url FROM kategorie ORDER BY id ASC");
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($categories as $category) {
                $sitemap[] = [
                    'url' => $config['site']['url'] . '/category/' . $category['url'],
                    'lastmod' => date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.7'
                ];
            }
        } catch (\Exception $e) {
            error_log('Sitemap Categories Error: ' . $e->getMessage());
        }
        
        return $sitemap;
    }
    
    /**
     * Generuje NewsArticle schema pro Google News
     */
    public static function generateNewsArticleSchema($article, $author = null)
    {
        $config = self::getConfig();
        $articleSchema = self::generateArticleSchema($article, $author);
        
        // Změna typu na NewsArticle
        $articleSchema['@type'] = 'NewsArticle';
        
        // Přidání specifických polí pro NewsArticle
        if (isset($article['datum'])) {
            $articleSchema['datePublished'] = date('c', strtotime($article['datum']));
        }
        
        // Přidání headline (povinné pro NewsArticle)
        $articleSchema['headline'] = $article['nazev'] ?? '';
        
        // Přidání image (povinné pro NewsArticle)
        if (isset($article['nahled_foto']) && $article['nahled_foto']) {
            $articleSchema['image'] = [
                '@type' => 'ImageObject',
                'url' => $config['site']['url'] . '/' . ltrim($article['nahled_foto'], '/'),
                'width' => $config['defaults']['image_width'] ?? 1200,
                'height' => $config['defaults']['image_height'] ?? 630
            ];
        }
        
        return $articleSchema;
    }
    
    /**
     * Generuje ImageObject schema
     */
    public static function generateImageSchema($imageUrl, $title = '', $caption = '')
    {
        $config = self::getConfig();
        
        return [
            '@type' => 'ImageObject',
            'url' => $imageUrl,
            'width' => $config['defaults']['image_width'] ?? 1200,
            'height' => $config['defaults']['image_height'] ?? 630,
            'title' => $title,
            'caption' => $caption
        ];
    }
    
    /**
     * Generuje VideoObject schema
     */
    public static function generateVideoSchema($videoUrl, $title, $description = '', $thumbnailUrl = '', $duration = '')
    {
        $config = self::getConfig();
        
        $schema = [
            '@type' => 'VideoObject',
            'name' => $title,
            'description' => $description,
            'uploadDate' => date('c'),
            'contentUrl' => $videoUrl,
            'embedUrl' => $videoUrl
        ];
        
        if ($thumbnailUrl) {
            $schema['thumbnailUrl'] = $thumbnailUrl;
        }
        
        if ($duration) {
            $schema['duration'] = $duration;
        }
        
        return $schema;
    }
    
    /**
     * Generuje Event schema
     */
    public static function generateEventSchema($event)
    {
        $config = self::getConfig();
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event['nazev'] ?? '',
            'description' => $event['popis'] ?? '',
            'startDate' => isset($event['datum_zacatku']) ? date('c', strtotime($event['datum_zacatku'])) : date('c'),
            'url' => $config['site']['url'] . '/events/' . ($event['url'] ?? '')
        ];
        
        if (isset($event['datum_konce'])) {
            $schema['endDate'] = date('c', strtotime($event['datum_konce']));
        }
        
        if (isset($event['misto'])) {
            $schema['location'] = [
                '@type' => 'Place',
                'name' => $event['misto']
            ];
        }
        
        return $schema;
    }
    
    /**
     * Generuje rozšířené Organization schema
     */
    public static function generateOrganizationSchema()
    {
        $config = self::getConfig();
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $config['structured_data']['organization']['name'],
            'url' => $config['structured_data']['organization']['url'],
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $config['structured_data']['organization']['logo']
            ],
            'description' => $config['structured_data']['organization']['description'],
            'foundingDate' => $config['structured_data']['organization']['foundingDate'],
            'address' => $config['structured_data']['organization']['address'],
            'contactPoint' => $config['structured_data']['organization']['contactPoint'],
            'sameAs' => $config['structured_data']['organization']['sameAs']
        ];
    }
    
    /**
     * Generuje WebSite schema s SearchAction
     */
    public static function generateWebSiteSchema()
    {
        $config = self::getConfig();
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $config['structured_data']['website']['name'],
            'url' => $config['structured_data']['website']['url'],
            'description' => $config['structured_data']['website']['description'],
            'inLanguage' => $config['structured_data']['website']['inLanguage'],
            'copyrightYear' => $config['structured_data']['website']['copyrightYear'],
            'publisher' => $config['structured_data']['website']['publisher'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $config['site']['url'] . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }
    
    /**
     * Generuje kompletní sadu meta tagů
     */
    public static function generateFullMetaTags($title, $description, $keywords = [], $image = null, $url = null, $type = 'website')
    {
        $config = self::getConfig();
        $url = $url ?? $config['site']['url'];
        
        return [
            'title' => self::generateTitle($title, null, $keywords),
            'description' => self::generateDescription(null, $description, $keywords),
            'keywords' => self::generateKeywords(null, $keywords),
            'robots' => self::generateRobotsMeta(),
            'canonical' => $url,
            'og' => self::generateOpenGraph($title, $description, $image, $type, $url),
            'twitter' => self::generateTwitterCard($title, $description, $image)
        ];
    }
}
