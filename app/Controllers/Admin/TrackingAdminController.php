<?php

namespace App\Controllers\Admin;

use App\Helpers\CsrfHelper;
use App\Helpers\TrackingHelper;

class TrackingAdminController
{
    /**
     * Zobrazí stránku pro správu tracking kódů
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $seoConfigFile = __DIR__ . '/../../../web/config/seo_config.json';
        $seoConfig = json_decode(file_get_contents($seoConfigFile), true);
        
        $trackingConfig = $seoConfig['tracking'] ?? [
            'meta_pixel_id' => 'YOUR_META_PIXEL_ID',
            'google_analytics_id' => 'YOUR_GA_ID',
            'enabled' => false
        ];

        $adminTitle = "Správa Tracking Kódů | Admin Panel - Cyklistickey magazín";
        $view = '../app/Views/Admin/tracking/index.php';
        include '../app/Views/Admin/layout/base.php';
    }

    /**
     * Uloží tracking konfiguraci
     */
    public function update()
    {
        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token';
            header('Location: /admin/tracking');
            exit;
        }

        try {
            $seoConfigFile = __DIR__ . '/../../../web/config/seo_config.json';
            $seoConfig = json_decode(file_get_contents($seoConfigFile), true);
            
            // Aktualizuj tracking konfiguraci
            $seoConfig['tracking'] = [
                'meta_pixel_id' => trim($_POST['meta_pixel_id'] ?? ''),
                'google_analytics_id' => trim($_POST['google_analytics_id'] ?? ''),
                'enabled' => isset($_POST['enabled'])
            ];

            // Ulož zpět do souboru
            $jsonContent = json_encode($seoConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($seoConfigFile, $jsonContent)) {
                $_SESSION['success'] = 'Tracking konfigurace byla úspěšně uložena';
            } else {
                $_SESSION['error'] = 'Chyba při ukládání konfigurace';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba: ' . $e->getMessage();
        }

        header('Location: /admin/tracking');
        exit;
    }

    /**
     * Testuje tracking kódy
     */
    public function test()
    {
        $metaPixelId = TrackingHelper::getMetaPixelId();
        $gaId = TrackingHelper::getGoogleAnalyticsId();
        $isEnabled = TrackingHelper::isTrackingEnabled();

        $results = [
            'meta_pixel' => [
                'id' => $metaPixelId,
                'valid' => !empty($metaPixelId) && $metaPixelId !== 'YOUR_META_PIXEL_ID',
                'enabled' => $isEnabled
            ],
            'google_analytics' => [
                'id' => $gaId,
                'valid' => !empty($gaId) && $gaId !== 'YOUR_GA_ID',
                'enabled' => $isEnabled
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}
