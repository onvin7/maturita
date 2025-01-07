<?php

namespace App\Controllers\Admin;

use App\Models\Article;
use App\Models\Statistics;

class HomeAdminController
{
    private $db;
    private $articleModel;
    private $statisticsModel;

    public function __construct($db)
    {
        $this->db = $db; // Připojení k databázi
        $this->articleModel = new Article($db); // Inicializace modelu článků
        $this->statisticsModel = new Statistics($db); // Inicializace modelu statistik
    }

    // Metoda pro zobrazení hlavní stránky admin panelu
    public function index()
    {
        $latestArticles = $this->articleModel->getAllAdmin(5); // 5 nejnovějších článků
        $mostReadArticles = $this->statisticsModel->getArticleViewsAdmin(); // Nejčtenější články
        $pageViewsData = $this->statisticsModel->getPageViewsAdmin(); // Data pro graf zobrazení stránek
        $articleViewsData = $this->statisticsModel->getArticleViewsAdmin(); // Data pro graf zobrazení článků

        $view = '../../app/Views/Admin/home/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }
}
