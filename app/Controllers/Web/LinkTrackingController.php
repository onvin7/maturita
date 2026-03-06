<?php

namespace App\Controllers\Web;

use App\Models\LinkClick;
use App\Models\LinkClickEvent;
use App\Helpers\UserAgentHelper;
use App\Helpers\GeoLocationHelper;
use App\Helpers\IPAnonymizerHelper;

class LinkTrackingController
{
    private $linkClickModel;
    private $linkClickEventModel;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->linkClickModel = new LinkClick($db);
        $this->linkClickEventModel = new LinkClickEvent($db);
    }

    /**
     * Zaznamená klik a přesměruje na cílovou URL
     */
    public function track($token)
    {
        // Dekódování tokenu (URL-safe base64 encoded JSON)
        // Převod z URL-safe base64 zpět na standardní base64
        $token = strtr($token, '-_', '+/');
        // Doplnění padding znaků
        $padding = (4 - strlen($token) % 4) % 4;
        $token = str_pad($token, strlen($token) + $padding, '=', STR_PAD_RIGHT);
        $data = json_decode(base64_decode($token), true);
        
        if (!$data || !isset($data['article_id']) || !isset($data['url'])) {
            http_response_code(400);
            die('Neplatný odkaz');
        }

        $articleId = (int)$data['article_id'];
        $url = $data['url'];
        $linkText = $data['link_text'] ?? null;
        $linkPosition = $data['link_position'] ?? null;
        $linkType = $data['link_type'] ?? null;

        // Zaznamenání kliku (agregovaný počet)
        $linkClickId = $this->linkClickModel->recordClick($articleId, $url, $linkText);

        // Check for cookie consent
        $cookieConsent = $_COOKIE['cookie_consent_status'] ?? null;
        // Pokud není cookie nastavena, předpokládáme nesouhlas (opt-in)
        $isTrackingAllowed = ($cookieConsent === 'granted');

        // Vždy sbíráme alespoň základní technické údaje pro účely bezpečnosti a statistiky, 
        // ale pro nesouhlasící uživatele je výrazně omezíme nebo anonymizujeme
        
        // Sběr detailních informací o kliku
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $ipAddress = UserAgentHelper::getClientIP();
        
        // Detekce zařízení, prohlížeče a OS
        $deviceType = UserAgentHelper::detectDeviceType($userAgent);
        $browser = UserAgentHelper::detectBrowser($userAgent);
        $os = UserAgentHelper::detectOS($userAgent);
        
        // Referrer
        $referrer = $_SERVER['HTTP_REFERER'] ?? null;
        
        // Session ID
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionId = session_id();
        
        // Geolokace (asynchronně, aby nezpomalovala redirect)
        // POZNÁMKA: Používáme původní IP pro geolokaci, anonymizujeme až před uložením
        $geoData = null;
        if ($isTrackingAllowed && $ipAddress && $deviceType !== 'bot') {
            // Použijeme rychlé volání, ale s timeoutem
            $geoData = GeoLocationHelper::getLocationFromIP($ipAddress);
        }
        
        // Data z GET parametrů (JavaScript tracking)
        $timeOnPage = isset($_GET['time']) ? (int)$_GET['time'] : null;
        $scrollDepth = isset($_GET['scroll']) ? (int)$_GET['scroll'] : null;
        $viewportWidth = isset($_GET['vw']) ? (int)$_GET['vw'] : null;
        $viewportHeight = isset($_GET['vh']) ? (int)$_GET['vh'] : null;

        // Anonymizace IP adresy pro GDPR kompatibilitu (před uložením do DB)
        // Pokud není souhlas, provedeme silnější anonymizaci nebo ji neuložíme vůbec
        if ($isTrackingAllowed) {
            $anonymizedIP = IPAnonymizerHelper::anonymizeIP($ipAddress);
        } else {
            // Bez souhlasu neukládáme IP ani v anonymizované podobě pro tracking
            $anonymizedIP = null; 
            // Bez souhlasu neukládáme Session ID pro tracking
            $sessionId = null;
        }

        // Příprava dat pro event
        $eventData = [
            'ip_address' => $anonymizedIP,
            'user_agent' => $userAgent, // User Agent je technický údaj nutný pro zobrazení, ale může být sporný. Pro statistiky "Mobile vs Desktop" je klíčový.
            'referrer' => $referrer,
            'session_id' => $sessionId,
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
            'country' => $geoData['country'] ?? null,
            'city' => $geoData['city'] ?? null,
            'time_on_page' => $timeOnPage,
            'link_position' => $linkPosition,
            'scroll_depth' => $scrollDepth,
            'link_type' => $linkType,
            'viewport_width' => $viewportWidth,
            'viewport_height' => $viewportHeight,
        ];

        // Uložení detailního eventu
        try {
            $this->linkClickEventModel->recordEvent($linkClickId, $articleId, $url, $eventData);
        } catch (\Exception $e) {
            // Logování chyby pro debugging
            $logFile = dirname(dirname(dirname(__DIR__))) . '/logs/link_tracking.log';
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            @file_put_contents($logFile, date('Y-m-d H:i:s') . " - LinkClickEvent error: " . $e->getMessage() . "\n", FILE_APPEND);
            @file_put_contents($logFile, date('Y-m-d H:i:s') . " - Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            error_log("LinkClickEvent error: " . $e->getMessage());
        }

        // Přesměrování na cílovou URL
        header('Location: ' . $url);
        exit;
    }
}

