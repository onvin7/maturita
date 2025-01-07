<?php

namespace App\Controllers\Public;

use App\Models\Article;

class ArticleController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Article($db);
    }

    // Zobrazení všech článků
    public function index()
    {
        $articles = $this->model->getAll();
        include '../app/Views/Public/articles/index.php';
    }

    // Zobrazení jednoho článku
    public function view($id)
    {
        $article = $this->model->getById($id);
        if (!$article) {
            header("HTTP/1.0 404 Not Found");
            echo "Článek nenalezen.";
            return;
        }
        $this->model->incrementViews($id);
        $categories = $this->model->getCategories($id);
        include '../../app/Views/Public/articles/view.php';
    }
}
