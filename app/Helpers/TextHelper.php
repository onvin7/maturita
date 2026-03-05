<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Zkrátí text na požadovanou délku a přidá tři tečky na konec, pokud je text zkrácen
     * 
     * @param string $text Text k zkrácení
     * @param int $maxLength Maximální délka textu
     * @param string $suffix Suffix který se přidá na konec zkráceného textu (výchozí "...")
     * @return string Zkrácený text
     */
    public static function truncate(string $text, int $maxLength, string $suffix = "..."): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $truncated = mb_substr($text, 0, $maxLength);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            return mb_substr($truncated, 0, $lastSpace) . $suffix;
        }

        return $truncated . $suffix;
    }

    /**
     * Zpracuje text a převede odkazy na sociální sítě na embedy
     * Podporuje: Instagram, Twitter/X, Facebook, YouTube, TikTok
     * 
     * @param string $content HTML obsah článku
     * @return string Upravený HTML obsah
     */
    public static function processEmbeds(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Helper function to clean URL from HTML tags if wrapped
        $cleanUrl = function($match) {
            return strip_tags($match);
        };

        // 1. Instagram (Post/Reel)
        // Hledá samostatné odkazy v <p> nebo přímo
        // Pattern: https://www.instagram.com/p/CODE/ nebo /reel/CODE/
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?instagram\.com\/(?:p|reel)\/([a-zA-Z0-9_-]+)\/?(?:\?[^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2]; // Full URL
                // Odstraníme query parametry pro čistší ID, ale pro embed stačí URL
                return '<blockquote class="instagram-media" data-instgrm-permalink="' . $url . '" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"></blockquote>';
            },
            $content
        );

        // 2. Twitter / X
        // Pattern: https://twitter.com/user/status/ID nebo https://x.com/user/status/ID
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?(?:twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/status\/([0-9]+)(?:\?[^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                // Nahradíme x.com za twitter.com pro kompatibilitu embedů, pokud je třeba, ale twitter widget obvykle zvládne obojí nebo přesměruje.
                // Pro jistotu standardizujeme na twitter.com pro embed
                $embedUrl = str_replace('x.com', 'twitter.com', $url);
                return '<blockquote class="twitter-tweet" align="center"><a href="' . $embedUrl . '"></a></blockquote>';
            },
            $content
        );

        // 3. Facebook (Post/Video)
        // Pattern: https://www.facebook.com/watch/?v=ID nebo https://www.facebook.com/PAGE/posts/ID
        // Použijeme fb-post nebo fb-video class
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.|web\.|m\.)?facebook\.com\/(?:[^<\s"\/]+\/posts\/[^<\s"\/]+|permalink\.php\?story_fbid=[^<\s"\&]+|watch\/\?v=\d+|[^<\s"\/]+\/videos\/[^<\s"\/]+)[^<\s"]*)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                $type = (strpos($url, 'watch') !== false || strpos($url, 'video') !== false) ? 'fb-video' : 'fb-post';
                return '<div class="' . $type . '" data-href="' . $url . '" data-width="500" data-show-text="true" style="margin: 0 auto; display: flex; justify-content: center;"></div>';
            },
            $content
        );

        // 4. TikTok
        // Pattern: https://www.tiktok.com/@user/video/ID
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?tiktok\.com\/@[^\/]+\/video\/(\d+)(?:\?[^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                $id = $matches[3];
                return '<blockquote class="tiktok-embed" cite="' . $url . '" data-video-id="' . $id . '" style="max-width: 605px;min-width: 325px;" > <section> <a target="_blank" title="@user" href="' . $url . '"></a> </section> </blockquote>';
            },
            $content
        );

        // 5. YouTube (Video & Shorts)
        // Pattern: https://www.youtube.com/watch?v=ID, https://youtu.be/ID, https://www.youtube.com/shorts/ID
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]+)(?:[&?][^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                $id = $matches[3];
                
                // Detekce Shorts pro správný poměr stran
                $isShorts = strpos($url, '/shorts/') !== false;
                $wrapperClass = $isShorts ? 'youtube-shorts-wrapper' : 'youtube-embed-wrapper';
                
                return '<div class="' . $wrapperClass . '"><iframe src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
            },
            $content
        );

        // 6. Strava (Activity)
        // Pattern: https://www.strava.com/activities/ID
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?strava\.com\/activities\/([0-9]+)(?:\?[^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $id = $matches[3];
                // Používáme oficiální Strava embed s výškou 405px
                return '<div class="strava-embed-placeholder" style="margin: 20px auto; max-width: 590px;"><iframe height="405" width="590" src="https://www.strava.com/activities/' . $id . '/embed/1" frameborder="0" allowtransparency="true" scrolling="no" style="max-width: 100%; width: 100%;"></iframe></div>';
            },
            $content
        );

        // 7. Reddit (Post/Comment)
        // Pattern: https://www.reddit.com/r/SUBREDDIT/comments/ID/TITLE/
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?reddit\.com\/r\/[^\/]+\/comments\/([a-zA-Z0-9]+)\/[^<\s"]*)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                // Očistíme URL od případných parametrů
                $url = strtok($url, '?');
                return '<blockquote class="reddit-card" data-card-created="1560000000"><a href="' . $url . '?ref=share&ref_source=embed"></a></blockquote>';
            },
            $content
        );

        // 8. Threads (Meta)
        // Pattern: https://www.threads.net/@user/post/ID
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:www\.)?threads\.net\/@[^\/]+\/post\/([a-zA-Z0-9_-]+)(?:\?[^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                return '<blockquote class="instagram-media" data-instgrm-permalink="' . $url . '" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);"></blockquote>';
            },
            $content
        );

        // 9. Pinterest (Pin)
        // Pattern: https://cz.pinterest.com/pin/ID/
        $content = preg_replace_callback(
            '/<p>\s*(<a[^>]*>)?\s*(https?:\/\/(?:[a-z]{2}\.)?pinterest\.com\/pin\/([0-9]+)\/?(?:\?[^<\s"]*)?)\s*(<\/a>)?\s*<\/p>/i',
            function($matches) {
                $url = $matches[2];
                // Pinterest vyžaduje specifický odkaz pro embed, ale často stačí jen link a JS to převede
                // Pro jistotu použijeme data-pin-do="embedPin"
                return '<a data-pin-do="embedPin" data-pin-width="medium" href="' . $url . '"></a>';
            },
            $content
        );

        return $content;
    }

    /**
     * Odstraní diakritiku z textu
     * 
     * @param string $string Text k úpravě
     * @return string Text bez diakritiky
     */
    private static function removeAccents(string $string): string
    {
        $table = [
            'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'í' => 'i',
            'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u',
            'ů' => 'u', 'ý' => 'y', 'ž' => 'z',
            'Á' => 'A', 'Č' => 'C', 'Ď' => 'D', 'É' => 'E', 'Ě' => 'E', 'Í' => 'I',
            'Ň' => 'N', 'Ó' => 'O', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ú' => 'U',
            'Ů' => 'U', 'Ý' => 'Y', 'Ž' => 'Z'
        ];
        return strtr($string, $table);
    }

    /**
     * Vygeneruje URL přátelský řetězec z daného textu
     * 
     * @param string $string Text k převedení na URL
     * @return string URL přátelský řetězec
     */
    public static function generateFriendlyUrl(string $string): string
    {
        $string = self::removeAccents($string); // Odstranění diakritiky
        $string = strtolower($string); // Převod na malá písmena
        $string = preg_replace("/[^a-z0-9\s-]/", "", $string); // Odstranění nežádoucích znaků
        $string = preg_replace("/\s+/", "-", $string); // Nahrazení mezer pomlčkou
        return $string;
    }
} 