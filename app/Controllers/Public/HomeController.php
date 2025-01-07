<?php

namespace App\Controllers\Public;

use App\Models\Category;
use App\Models\Article;

class HomeController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        $categoryModel = new Category($this->db);
        $articleModel = new Article($this->db);

        // Načtení dat
        $latestArticles = $articleModel->getLatestArticles(5); // Nejnovější články
        $topCategories = $categoryModel->getAll(3); // 3 nejčastější kategorie

        // Předání dat do šablony
        $view = '../app/Views/Public/home/index.php';
        include $view;
    }



    public function listByCategory($categoryUrl)
    {
        $categoryModel = new Category($this->db);

        // Získání kategorie podle URL
        $category = $categoryModel->getByUrl($categoryUrl);

        if (!$category) {
            include '../app/Views/Public/home/articleNotFound.php';
            return;
        }

        // Získání článků podle ID kategorie
        $articles = $categoryModel->getArticlesByCategory($category['id']);

        include '../app/Views/Public/home/listByCategory.php';
    }

    public function articleDetail($articleUrl)
    {
        $articleModel = new Article($this->db);

        // Získání článku podle URL
        $article = $articleModel->getByUrl($articleUrl);

        if (!$article) {
            include '../app/Views/Public/home/articleNotFound.php';
            return;
        }

        include '../app/Views/Public/home/articleDetail.php';
    }
}
