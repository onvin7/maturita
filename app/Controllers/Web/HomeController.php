<?php

namespace App\Controllers\Web;

use App\Models\Category;
use App\Models\Article;
use App\Helpers\SEOHelper;
use App\Helpers\RedirectHelper;

class HomeController
{
    private $db;
    private $articleModel;
    private $categoryModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->articleModel = new Article($db);
        $this->categoryModel = new Category($db);
    }

    public function index()
    {
        $main_article = $this->articleModel->getNewestArticle();
        $articles = $this->articleModel->getLatestArticles(4, 0);
        $categories = $this->articleModel->getCategoriesWithArticlesSorted();

        if (!is_array($categories)) {
            $categories = [];
        }

        $css = ['main-page'];
        
        // SEO nastavení pro hlavní stránku
        $keywords = ["cyklistika", "kolo", "závody", "trénink", "technika", "novinky"];
        $title = "Cyklistický magazín – Novinky, závody a technika";
        $description = "Sledujte nejnovější zprávy, tréninkové tipy, technické novinky a rozhovory ze světa cyklistiky.";
        $canonicalPath = "";
        $canonicalUrl = SEOHelper::generateCanonicalUrl($canonicalPath);
        
        // Breadcrumbs pro hlavní stránku
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/']
        ];
        
        // Structured data pro webovou stránku
        $structuredData = SEOHelper::generateWebSiteSchema();
        
        // Přidání Organization schema
        $organizationSchema = SEOHelper::generateOrganizationSchema();
        
        // Kombinace structured data
        $structuredData = [
            $structuredData,
            $organizationSchema,
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];

        $view = '../app/Views/Web/home/index.php';
        require '../app/Views/Web/layouts/base.php';
    }

    public function kontakt()
    {
        $css = ['kontakt'];
        $script = ['kontakt'];
        
        // SEO nastavení
        $keywords = ["kontakt", "redakce", "cyklistika", "dotazy", "spolupráce"];
        $title = "Kontakt | Cyklistický magazín";
        $description = "Kontaktujte redakci Cyklistického magazínu. Jsme tu pro vaše dotazy, návrhy, či spolupráci.";
        $ogTitle = "Kontaktujte nás | Cyklistický magazín";
        $ogDescription = "Máte dotaz nebo návrh? Kontaktujte redakci Cyklistického magazínu a budeme rádi za vaši zpětnou vazbu.";
        $canonicalUrl = SEOHelper::generateCanonicalUrl("kontakt");
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Kontakt', 'url' => '/kontakt']
        ];
        
        // Structured data pro kontaktní stránku
        $structuredData = [
            "@context" => "https://schema.org",
            "@type" => "ContactPage",
            "name" => "Kontaktní stránka - Cyklistický magazín",
            "url" => $canonicalUrl
        ];
        
        // Přidání Organization schema
        $structuredData = [
            $structuredData,
            SEOHelper::generateOrganizationSchema(),
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];

        $view = '../app/Views/Web/home/kontakt.php';
        require '../app/Views/Web/layouts/base.php';
    }

    public function race()
    {
        // 301 Redirect na novou /events URL (zachování SEO)
        RedirectHelper::permanent('/events');
    }

    public function raceCyklistickey()
    {
        // 301 Redirect na novou /events URL (zachování SEO)
        RedirectHelper::permanent('/events');
    }
    
    public function raceBezeckey()
    {
        // 301 Redirect na novou /events URL (zachování SEO)
        RedirectHelper::permanent('/events');
    }

    public function events()
    {
        // SEO nastavení
        $keywords = ["events", "závody", "akce", "události", "cyklistika", "kalendář"];
        $title = "Events | Cyklistický magazín";
        $description = "Přehled všech akcí a událostí souvisejících s cyklistikou - závody, výstavy, workshopy a další.";
        $ogTitle = "Events - Cyklistické akce a události";
        $ogDescription = "Kalendář cyklistických akcí, závodů, výstav a dalších událostí pro všechny milovníky cyklistiky.";
        $canonicalPath = "events";
        $canonicalUrl = SEOHelper::generateCanonicalUrl($canonicalPath);
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Events', 'url' => '/events']
        ];
        
        // Structured data pro seznam eventů
        $structuredData = [
            "@context" => "https://schema.org",
            "@type" => "CollectionPage",
            "name" => "Events - Cyklistické akce a události",
            "url" => $canonicalUrl,
            "description" => $description
        ];
        
        // Přidání breadcrumb schema
        $structuredData = [
            $structuredData,
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];
        
        $css = ['kategorie', 'events'];

        $view = '../app/Views/Web/events/index.php';
        require '../app/Views/Web/layouts/base.php';
    }

    public function eventDetail($year, $name)
    {
        // Mapování názvů eventů na existující views
        $eventViews = [
            'cyklistickey' => '../app/Views/Web/race/cyklistickey_race.php',
            'bezeckey' => '../app/Views/Web/race/bezeckey_race.php',
        ];

        if (!isset($eventViews[$name])) {
            http_response_code(404);
            // SEO pro 404 - noindex, nofollow
            $title = "Event nenalezen | Cyklistický magazín";
            $description = "Požadovaný event nebyl nalezen. Zkuste navštívit stránku s přehledem eventů.";
            $robotsMeta = 'noindex, nofollow';
            $view = "../app/Views/Web/templates/404.php";
            require "../app/Views/Web/layouts/base.php";
            exit;
        }

        // SEO nastavení
        $eventTitle = ucfirst($name) . " Race " . $year;
        $keywords = ["závod", "race", $name, $year, "cyklistika", "běh", "událost"];
        $title = $eventTitle . " | Cyklistický magazín";
        $description = "Detailní informace o závodě " . $eventTitle . ", trasy, pravidla a praktické informace pro závodníky.";
        $ogTitle = $eventTitle . " - Závod pro všechny cyklistické nadšence";
        $ogDescription = "Kompletní informace o závodě " . $eventTitle . " - trasy, registrace, pravidla a praktické informace.";
        $canonicalPath = "events/" . $year . "/" . $name;
        $canonicalUrl = SEOHelper::generateCanonicalUrl($canonicalPath);
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Events', 'url' => '/events'],
            ['name' => $eventTitle, 'url' => '/events/' . $year . '/' . $name]
        ];
        
        // Structured data pro event
        $eventData = [
            'nazev' => $eventTitle,
            'popis' => $description,
            'datum_zacatku' => $year . '-01-01',
            'url' => $name
        ];
        $structuredData = [
            SEOHelper::generateEventSchema($eventData),
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];
        
        $css = ['race'];

        $view = $eventViews[$name];
        require '../app/Views/Web/layouts/base.php';
    }

    public function viewLog($logFileName)
    {
        // Bezpečnostní kontrola - povolíme pouze .log soubory
        if (!preg_match('/^[a-zA-Z0-9_-]+\.log$/', $logFileName)) {
            http_response_code(400);
            die('Neplatný název log souboru');
        }

        // Cesta k log souborům - logs/ může být v rootu projektu nebo o úroveň výš
        // app/Controllers/Web -> app -> root -> logs/
        // NEBO app/Controllers/Web -> app -> root -> .. -> logs/ (subdom/logs/)
        $rootPath = dirname(dirname(dirname(__DIR__)));
        
        // Zkusíme nejdřív o úroveň výš (subdom/logs/), pak v rootu (bicenc/logs/)
        $possiblePaths = [
            dirname($rootPath) . '/logs/' . $logFileName,  // subdom/logs/
            $rootPath . '/logs/' . $logFileName,           // bicenc/logs/
        ];
        
        $logPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $logPath = $path;
                break;
            }
        }
        
        // Pokud žádná cesta neexistuje, použijeme první
        if (!$logPath) {
            $logPath = $possiblePaths[0];
        }
        
        // Kontrola existence souboru
        if (!file_exists($logPath)) {
            error_log("DEBUG LOG VIEWER: Log file not found at: " . $logPath);
            error_log("DEBUG LOG VIEWER: Root path: " . $rootPath);
            error_log("DEBUG LOG VIEWER: __DIR__: " . __DIR__);
            http_response_code(404);
            die('Log soubor nenalezen');
        }

        // Načtení obsahu souboru
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Obrátit pořadí - nejnovější první
        $lines = array_reverse($lines);

        // Přímé zobrazení bez layoutu
        $view = '../app/Views/Web/logs/view.php';
        require $view;
        exit;
    }

    public function appka()
    {
        // SEO nastavení
        $keywords = ["aplikace", "app", "mobilní", "cyklistika", "novinky", "trasy"];
        $title = "Appka | Cyklistický magazín";
        $description = "Články a aktuality ze všech koutů cyklistiky. Vše hezky na jednom místě.";
        $ogTitle = "Cyklistickey App - Mobilní aplikace pro cyklisty";
        $ogDescription = "Mobilní aplikace Cyklistickey - novinky, trasy, závody a vše o cyklistice na jednom místě.";
        $canonicalPath = "appka";
        $canonicalUrl = SEOHelper::generateCanonicalUrl($canonicalPath);
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Appka', 'url' => '/appka']
        ];
        
        // Structured data pro stránku aplikace
        $structuredData = [
            "@context" => "https://schema.org",
            "@type" => "WebPage",
            "name" => "Cyklistickey App - Mobilní aplikace",
            "url" => $canonicalUrl,
            "description" => $description
        ];
        
        // Přidání breadcrumb schema
        $structuredData = [
            $structuredData,
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];
        
        $css = ['appka'];

        $view = '../app/Views/Web/home/appka.php';
        require '../app/Views/Web/layouts/base.php';
    }

    public function manual()
    {
        // SEO nastavení - interní stránka, nechceme indexovat
        // Pro čistou stránku nepotřebujeme base.php ani SEO proměnné
        
        require '../app/Views/Web/home/manual.php';
    }

    public function obchodniPodminky()
    {
        $css = ['kontakt'];
        
        // SEO nastavení
        $keywords = ["obchodní podmínky", "podmínky", "pravidla", "cyklistika"];
        $title = "Obchodní podmínky | Cyklistický magazín";
        $description = "Obchodní podmínky Cyklistického magazínu. Pravidla a podmínky používání našich služeb.";
        $ogTitle = "Obchodní podmínky | Cyklistický magazín";
        $ogDescription = "Přečtěte si obchodní podmínky a pravidla používání služeb Cyklistického magazínu.";
        $canonicalUrl = SEOHelper::generateCanonicalUrl("obchodni-podminky");
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Obchodní podmínky', 'url' => '/obchodni-podminky']
        ];
        
        // Structured data
        $structuredData = [
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];

        $view = '../app/Views/Web/home/obchodni-podminky.php';
        require '../app/Views/Web/layouts/base.php';
    }

    public function ochranaOsobnichUdaju()
    {
        $css = ['kontakt'];
        
        // SEO nastavení
        $keywords = ["ochrana osobních údajů", "GDPR", "soukromí", "osobní údaje"];
        $title = "Ochrana osobních údajů | Cyklistický magazín";
        $description = "Zásady ochrany osobních údajů a zpracování osobních údajů v souladu s GDPR.";
        $ogTitle = "Ochrana osobních údajů | Cyklistický magazín";
        $ogDescription = "Informace o zpracování a ochraně osobních údajů v souladu s nařízením GDPR.";
        $canonicalUrl = SEOHelper::generateCanonicalUrl("ochrana-osobnich-udaju");
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Ochrana osobních údajů', 'url' => '/ochrana-osobnich-udaju']
        ];
        
        // Structured data
        $structuredData = [
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];

        $view = '../app/Views/Web/home/ochrana-osobnich-udaju.php';
        require '../app/Views/Web/layouts/base.php';
    }

    public function testLogs()
    {
        // Test zapsání do logs/
        $possibleLogPaths = [
            dirname(dirname(dirname(__DIR__))) . '/logs/debug_test.log',  // bicenc/logs/
            dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/debug_test.log',  // subdom/logs/
        ];

        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test logs</title></head><body>";
        echo "<h1>Test zápisu do logs/</h1>";
        echo "<p>__DIR__: " . __DIR__ . "</p>";
        echo "<p>dirname(dirname(dirname(__DIR__))): " . dirname(dirname(dirname(__DIR__))) . "</p>";
        echo "<p>dirname(dirname(dirname(dirname(__DIR__)))): " . dirname(dirname(dirname(dirname(__DIR__)))) . "</p>";

        foreach ($possibleLogPaths as $path) {
            $dir = dirname($path);
            echo "<h2>Test: $path</h2>";
            echo "<p>Adresář: $dir</p>";
            echo "<p>Adresář existuje: " . (is_dir($dir) ? 'ANO' : 'NE') . "</p>";
            echo "<p>Adresář je zapisovatelný: " . (is_writable($dir) ? 'ANO' : 'NE') . "</p>";
            
            if (!is_dir($dir)) {
                echo "<p>Vytváření adresáře...</p>";
                if (@mkdir($dir, 0755, true)) {
                    echo "<p style='color: green;'>✓ Adresář vytvořen</p>";
                } else {
                    echo "<p style='color: red;'>✗ Chyba při vytváření adresáře</p>";
                }
            }
            
            $testContent = date('Y-m-d H:i:s') . " - TEST ZÁPIS\n";
            if (@file_put_contents($path, $testContent, FILE_APPEND)) {
                echo "<p style='color: green;'>✓ Zápis úspěšný</p>";
                if (file_exists($path)) {
                    echo "<p>Obsah souboru (posledních 20 řádků):</p>";
                    $lines = file($path);
                    $lastLines = array_slice($lines, -20);
                    echo "<pre>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
                }
            } else {
                echo "<p style='color: red;'>✗ Chyba při zápisu</p>";
                $error = error_get_last();
                if ($error) {
                    echo "<p>Chyba: " . htmlspecialchars($error['message']) . "</p>";
                }
            }
            echo "<hr>";
        }
        echo "</body></html>";
        exit;
    }
}
