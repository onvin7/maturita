<?php

namespace App\Controllers\Admin;

use App\Models\Statistics;

class StatisticsAdminController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Statistics($db);
    }

    // Zobrazení statistik všech článků
    public function index()
    {
        $articleViews = $this->model->getAllArticleViews();
        $view = '../../app/Views/Admin/statistics/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    // Detail statistik konkrétního článku
    public function view($articleId)
    {
        $articleViews = $this->model->getArticleViewsById($articleId);
        $view = '../../app/Views/Admin/statistics/view.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    // Nejčtenější články
    public function top()
    {
        $topArticles = $this->model->getTopArticles();
        $view = '../../app/Views/Admin/statistics/top.php';
        include '../../app/Views/Admin/layout/base.php';
    }
}
