<?php

namespace App\Controllers\Web;

use App\Models\Article;
use App\Helpers\TextHelper;
use App\Helpers\SEOHelper;

class SearchController
{
    private $db;
    private $articleModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->articleModel = new Article($db);
    }

    public function index()
    {
        $query = $_GET['q'] ?? '';
        $query = trim($query);

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        $perPage = 12;
        
        $results = [];
        $totalResults = 0;
        $totalPages = 0;
        
        if (!empty($query)) {
            $totalResults = $this->articleModel->searchCount($query);
            $totalPages = (int) ceil($totalResults / $perPage);
            if ($totalPages < 1) {
                $totalPages = 1;
            }
            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $offset = ($page - 1) * $perPage;
            $rawResults = $this->articleModel->search($query, $perPage, $offset);
            
            foreach ($rawResults as $article) {
                $article['nazev_highlighted'] = preg_replace(
                    '/' . preg_quote($query, '/') . '/i',
                    '<span class="search-highlight">$0</span>',
                    $article['nazev']
                );

                $article['snippet'] = TextHelper::getSearchSnippet($article['obsah'], $query);

                $results[] = $article;
            }
        }

        // SEO settings
        $title = 'Výsledky vyhledávání' . (!empty($query) ? ': ' . htmlspecialchars($query) : '') . ' | Cyklistický magazín';
        $description = 'Výsledky vyhledávání pro dotaz: ' . htmlspecialchars($query);
        $canonicalUrl = SEOHelper::generateCanonicalUrl('search');
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Domů', 'url' => '/'],
            ['name' => 'Vyhledávání', 'url' => '/search']
        ];
        
        // CSS - přidáme search.css pokud bude potřeba, jinak stačí styly v hlavičce nebo main-page
        $css = ['main-page', 'search', 'kategorie']; 

        // Structured data
        $structuredData = [
            SEOHelper::generateBreadcrumbSchema($breadcrumbs)
        ];

        $view = '../app/Views/Web/search/index.php';
        require '../app/Views/Web/layouts/base.php';
    }
}
