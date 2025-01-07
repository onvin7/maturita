<?php

namespace App\Controllers\Public;

use App\Models\Category;

class CategoryController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Category($db);
    }

    // Zobrazení všech kategorií
    public function index()
    {
        $categories = $this->model->getAll();

        // Správná cesta k šabloně
        $view = '../app/Views/Public/categories/index.php';
        include $view;
    }


    // Zobrazení jedné kategorie
    public function view($id)
    {
        $category = $this->model->getById($id);
        include '../app/Views/Public/categories/view.php';
    }
}
