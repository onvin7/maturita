<?php

namespace App\Controllers\Web;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Helpers\SEOHelper;
use App\Helpers\LinkTrackingHelper;

class ArticleController
{
    private $articleModel;
    private $categoryModel;
    private $userModel;

    public function __construct($db)
    {
        $this->articleModel = new Article($db);
        $this->categoryModel = new Category($db);
        $this->userModel = new User($db);
    }

    // Zobrazení všech článků
    public function index()
    {
        $articles = $this->articleModel->getAllWithAuthors();
        $css = ["main-page", "kategorie"];
        
        // SEO nastavení
        $keywords = ["články", "cyklistika", "novinky", "závody", "trénink", "technika"];
        $title = "Články | Cyklistický magazín";
        $description = "Přehled všech článků z cyklistického magazínu - novinky, závody, tréninkové tipy a technické články.";
        $canonicalPath = "articles";
        $canonicalUrl = SEOHelper::generateCanonicalUrl($canonicalPath);
        
        // Breadcrumbs pro seznam článků
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Články', 'url' => '/articles']
        ];
        
        // Structured data pro seznam článků
        $structuredData = [
            "@context" => "https://schema.org",
            "@type" => "CollectionPage",
            "name" => "Články - Cyklistický magazín",
            "url" => $canonicalUrl,
            "description" => $description
        ];
        
        // Přidání breadcrumb schema
        $structuredData = [
            $structuredData,
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];

        $view = '../app/Views/Web/articles/index.php';
        require '../app/Views/Web/layouts/base.php';
    }

    // Zobrazení jednoho článku
    public function articleDetail($url)
    {
        $article = $this->articleModel->getByUrl($url);
        if (!$article) {
            header("HTTP/1.0 404 Not Found");
            // SEO pro 404 - noindex, nofollow
            $title = "Stránka nenalezena | Cyklistický magazín";
            $description = "Hledaný článek nebyl nalezen. Zkuste navštívit hlavní stránku nebo použít vyhledávání.";
            $robotsMeta = 'noindex, nofollow';
            
            $view = '../app/Views/Web/templates/404.php';
            require '../app/Views/Web/layouts/base.php';
            exit;
        }

        $this->articleModel->incrementViews($article['id']);

        // Získání souvisejících článků s ošetřením, pokud nejsou žádné nalezeny
        $relatedArticles = $this->articleModel->getRelatedArticles($article['id'], 3);
        // Kontrola, zda jsou related articles pole
        if (!is_array($relatedArticles)) {
            $relatedArticles = [];
        }

        $author = $this->userModel->getById($article['user_id']);
        
        // SEO nastavení
        $title = isset($article['nazev']) ? $article['nazev'] : "Cyklistický magazín";
        $canonicalPath = "article/" . (isset($article['url']) ? $article['url'] : "");
        $canonicalUrl = SEOHelper::generateCanonicalUrl($canonicalPath);
        $ogImage = isset($article['nahled_foto']) && $article['nahled_foto'] ? SEOHelper::generateCanonicalUrl('uploads/thumbnails/velke/' . $article['nahled_foto']) : null;
        $keywords = [];
        
        // Extrahuj klíčová slova z obsahu článku
        if (isset($article['obsah'])) {
            $keywords = SEOHelper::extractKeywords($article['obsah'], 8);
        }
        
        // Generuj description pomocí SEOHelper
        $description = SEOHelper::generateDescription($article['obsah'] ?? null, null, $keywords);
        
        // Article meta tags
        $articlePublishedTime = isset($article['datum']) ? date('c', strtotime($article['datum'])) : null;
        $articleModifiedTime = isset($article['updated_at']) ? date('c', strtotime($article['updated_at'])) : $articlePublishedTime;
        $articleAuthor = $author ? ($author['name'] . ' ' . $author['surname']) : null;
        
        // Article section (první kategorie) a tags (všechny kategorie)
        $articleSection = null;
        $articleTags = [];
        if (isset($article['kategorie']) && is_array($article['kategorie']) && !empty($article['kategorie'])) {
            // První kategorie jako section
            $articleSection = $article['kategorie'][0]['nazev_kategorie'] ?? null;
            // Všechny kategorie jako tags
            foreach ($article['kategorie'] as $kategorie) {
                if (isset($kategorie['nazev_kategorie'])) {
                    $articleTags[] = $kategorie['nazev_kategorie'];
                }
            }
        }
        
        // Breadcrumbs pro článek
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Články', 'url' => '/articles'],
            ['name' => $title, 'url' => '/article/' . $article['url']]
        ];
        
        // Structured data pro článek - Article + NewsArticle (pokud je novinka do 3 dnů)
        $articleDate = isset($article['datum']) ? strtotime($article['datum']) : time();
        $daysSincePublication = (time() - $articleDate) / (60 * 60 * 24);
        
        $structuredData = [];
        
        if ($daysSincePublication <= 3) {
            // Novinka - použij NewsArticle
            $structuredData[] = SEOHelper::generateNewsArticleSchema($article, $author);
        } else {
            // Starší článek - použij Article
            $structuredData[] = SEOHelper::generateArticleSchema($article, $author);
        }
        
        // Přidání breadcrumb schema
        $structuredData[] = SEOHelper::generateBreadcrumbSchema($breadcrumbs);
        
        // Přidání ImageObject pokud existuje obrázek
        if ($ogImage) {
            $structuredData[] = SEOHelper::generateImageSchema($ogImage, $title, $description);
        }
        
        // Načtení audio z databáze, pokud existuje
        if (!empty($article['audio'])) {
            $audioUrl = $article['audio'];
        } else {
            // Zpětná kompatibilita: kontrola existence souboru na disku
            $audioFilePath = __DIR__ . '/../../../web/uploads/audio/' . $article['id'] . '.mp3';
            $fileExists = @file_exists($audioFilePath);
            
            if ($fileExists) {
                $audioUrl = '/uploads/audio/' . $article['id'] . '.mp3';
            } else {
                $audioUrl = null;
            }
        }
        
        // Cesta k empty_clanek.php pro případ, že nejsou nalezeny žádné články
        $emptyArticlePath = '../app/Views/Web/templates/empty_clanek.php';

        // Přidání trackingu k odkazům v obsahu článku
        if (isset($article['obsah'])) {
            $article['obsah'] = LinkTrackingHelper::addTrackingToLinks($article['obsah'], $article['id']);
        }

        $css = ["main-page", "clanek", "autor_clanku"];

        $view = '../app/Views/Web/articles/article.php';
        require '../app/Views/Web/layouts/base.php';
    }
}