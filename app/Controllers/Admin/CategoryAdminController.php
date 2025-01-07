<?php

namespace App\Controllers\Admin;

use App\Models\Category;

class CategoryAdminController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new Category($db);
    }

    public function index()
    {
        $categories = $this->model->getAll();
        $view = '../../app/Views/Admin/categories/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    public function create()
    {
        $view = '../../app/Views/Admin/categories/create.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    public function store($postData)
    {
        if (empty($postData['nazev_kategorie'])) {
            echo "Název kategorie je povinný.";
            return;
        }

        $data = [
            'nazev_kategorie' => $postData['nazev_kategorie'],
            'url' => strtolower(preg_replace('/\s+/', '-', trim($postData['nazev_kategorie'])))
        ];

        $result = $this->model->create($data);

        if ($result) {
            header("Location: /admin/categories");
            exit;
        } else {
            echo "Chyba při ukládání kategorie.";
        }
    }

    public function edit($id)
    {
        $category = $this->model->getById($id);
        if (!$category) {
            echo "Kategorie nenalezena.";
            return;
        }

        $view = '../../app/Views/Admin/categories/edit.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    public function update($id, $postData)
    {
        if (empty($postData['nazev_kategorie'])) {
            echo "Název kategorie je povinný.";
            return;
        }

        $data = [
            'id' => $id,
            'nazev_kategorie' => $postData['nazev_kategorie'],
            'url' => strtolower(preg_replace('/\s+/', '-', trim($postData['nazev_kategorie'])))
        ];

        $result = $this->model->update($data);

        if ($result) {
            header("Location: /admin/categories");
            exit;
        } else {
            echo "Chyba při aktualizaci kategorie.";
        }
    }

    public function delete($id)
    {
        $result = $this->model->delete($id);

        if ($result) {
            header("Location: /admin/categories");
            exit;
        } else {
            echo "Chyba při mazání kategorie.";
        }
    }
}
