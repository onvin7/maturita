<?php

namespace App\Helpers;

class TrackingHelper
{
    /**
     * Generuje Meta Pixel kód
     */
    public static function generateMetaPixel($pixelId = null)
    {
        if (!$pixelId) {
            $seoConfig = self::getSEOConfig();
            $pixelId = $seoConfig['tracking']['meta_pixel_id'] ?? null;
        }

        if (!$pixelId || $pixelId === 'YOUR_META_PIXEL_ID') {
            return '';
        }

        return "
<!-- Meta Pixel Code -->
<script type=\"text/plain\" data-cookie-consent=\"tracking\">
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
fbq('track', 'PageView');
</script>
<noscript><img height=\"1\" width=\"1\" style=\"display:none\"
src=\"https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1\"
/></noscript>
<!-- End Meta Pixel Code -->";
    }

    /**
     * Generuje Google Analytics kód
     */
    public static function generateGoogleAnalytics($gaId = null)
    {
        if (!$gaId) {
            $seoConfig = self::getSEOConfig();
            $gaId = $seoConfig['tracking']['google_analytics_id'] ?? null;
        }

        if (!$gaId || $gaId === 'YOUR_GA_ID') {
            return '';
        }

        return "
<!-- Google Analytics -->
<script type=\"text/plain\" data-cookie-consent=\"tracking\" async src=\"https://www.googletagmanager.com/gtag/js?id={$gaId}\"></script>
<script type=\"text/plain\" data-cookie-consent=\"tracking\">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$gaId}');
</script>
<!-- End Google Analytics -->";
    }

    /**
     * Generuje Meta Pixel event tracking
     */
    public static function generateMetaPixelEvent($eventName, $parameters = [])
    {
        $seoConfig = self::getSEOConfig();
        $pixelId = $seoConfig['tracking']['meta_pixel_id'] ?? null;

        if (!$pixelId || $pixelId === 'YOUR_META_PIXEL_ID') {
            return '';
        }

        $paramsJson = json_encode($parameters);
        return "<script type=\"text/plain\" data-cookie-consent=\"tracking\">fbq('track', '{$eventName}', {$paramsJson});</script>";
    }

    /**
     * Generuje Google Analytics event tracking
     */
    public static function generateGAEvent($eventName, $parameters = [])
    {
        $seoConfig = self::getSEOConfig();
        $gaId = $seoConfig['tracking']['google_analytics_id'] ?? null;

        if (!$gaId || $gaId === 'YOUR_GA_ID') {
            return '';
        }

        $paramsJson = json_encode($parameters);
        return "<script type=\"text/plain\" data-cookie-consent=\"tracking\">gtag('event', '{$eventName}', {$paramsJson});</script>";
    }

    /**
     * Generuje kompletní tracking kód
     */
    public static function generateTrackingCode()
    {
        $seoConfig = self::getSEOConfig();
        
        if (!($seoConfig['tracking']['enabled'] ?? false)) {
            return '';
        }

        $metaPixel = self::generateMetaPixel();
        $googleAnalytics = self::generateGoogleAnalytics();

        return $metaPixel . $googleAnalytics;
    }

    /**
     * Načte SEO konfiguraci
     */
    private static function getSEOConfig()
    {
        static $config = null;
        
        if ($config === null) {
            $configFile = __DIR__ . '/../../web/config/seo_config.json';
            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
            } else {
                $config = [];
            }
        }
        
        return $config;
    }

    /**
     * Zkontroluje, jestli je tracking povolený
     */
    public static function isTrackingEnabled()
    {
        $seoConfig = self::getSEOConfig();
        return $seoConfig['tracking']['enabled'] ?? false;
    }

    /**
     * Získá Meta Pixel ID
     */
    public static function getMetaPixelId()
    {
        $seoConfig = self::getSEOConfig();
        return $seoConfig['tracking']['meta_pixel_id'] ?? null;
    }

    /**
     * Získá Google Analytics ID
     */
    public static function getGoogleAnalyticsId()
    {
        $seoConfig = self::getSEOConfig();
        return $seoConfig['tracking']['google_analytics_id'] ?? null;
    }
}
