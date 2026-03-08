<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Helpers\LogHelper;

use App\Helpers\CsrfHelper; // Přidáno

class CategoryAdminController
{
    // ...

    public function store($postData)
    {
        // CSRF kontrola
        if (!CsrfHelper::verify($postData['csrf_token'] ?? '')) {
            die("Chyba: Neplatný bezpečnostní token (CSRF). Zkuste formulář odeslat znovu.");
        }

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
            @LogHelper::admin('Category created', 'Name: ' . ($data['nazev_kategorie'] ?? 'N/A'));
            header("Location: /admin/categories");
            exit;
        } else {
            @LogHelper::admin('Category create failed', 'Name: ' . ($data['nazev_kategorie'] ?? 'N/A'));
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

        $adminTitle = "Upravit kategorii: " . $category['nazev_kategorie'] . " | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/categories/edit.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function update($id, $postData)
    {
        // CSRF kontrola
        if (!CsrfHelper::verify($postData['csrf_token'] ?? '')) {
            die("Chyba: Neplatný bezpečnostní token (CSRF). Zkuste formulář odeslat znovu.");
        }

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
            @LogHelper::admin('Category updated', 'ID: ' . $id . ', Name: ' . ($data['nazev_kategorie'] ?? 'N/A'));
            header("Location: /admin/categories");
            exit;
        } else {
            @LogHelper::admin('Category update failed', 'ID: ' . $id);
            echo "Chyba při aktualizaci kategorie.";
        }
    }

    public function delete($id)
    {
        $result = $this->model->delete($id);

        if ($result) {
            @LogHelper::admin('Category deleted', 'ID: ' . $id);
            header("Location: /admin/categories");
            exit;
        } else {
            @LogHelper::admin('Category delete failed', 'ID: ' . $id);
            echo "Chyba při mazání kategorie.";
        }
    }
}
