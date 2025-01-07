<?php

namespace App\Controllers\Admin;

use App\Models\Article;
use App\Models\Category;

class ArticleAdminController
{
    private $model;
    private $articleModel;

    public function __construct($db)
    {
        $this->model = $db;
        $this->articleModel = new Article($db);
    }

    public function index()
    {
        $sortBy = $_GET['sort_by'] ?? 'datum'; // Výchozí řazení podle data
        $order = $_GET['order'] ?? 'DESC';    // Výchozí sestupné řazení
        $filter = $_GET['filter'] ?? '';      // Výchozí bez filtru

        $articles = $this->articleModel->getAllWithSortingAndFiltering($sortBy, $order, $filter);

        $view = '../../app/Views/Admin/articles/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }


    // Formulář pro vytvoření článku
    public function create()
    {
        $categoryModel = new Category($this->model); // Použití modelu kategorie
        $categories = $categoryModel->getAll(); // Načtení kategorií
        $view = '../../app/Views/Admin/articles/create.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    // Ukládání nového článku
    public function store($postData)
    {
        if (empty($postData['nazev']) || empty($postData['obsah'])) {
            echo "Název a obsah článku jsou povinné.";
            return;
        }

        // Zpracování nahrání souboru
        $nahledFoto = "default.jpg ";
        $targetDir = __DIR__ . '../../../uploads/thumbnails/';

        // Kontrola a vytvoření složky, pokud neexistuje
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (isset($_FILES['nahled_foto']) && $_FILES['nahled_foto']['error'] === UPLOAD_ERR_OK) {
            $uniqueName =  basename($_FILES['nahled_foto']['name']);
            $targetFile = $targetDir . $uniqueName;

            if (move_uploaded_file($_FILES['nahled_foto']['tmp_name'], $targetFile)) {
                $nahledFoto = $uniqueName;
                echo "<p>Fotka byla úspěšně nahrána:</p>";
                echo "<img src='/uploads/thumbnails/$nahledFoto' alt='Náhled' style='max-width: 150px;'>";
            } else {
                echo "Chyba při nahrávání souboru.";
                return;
            }
        }

        $data = [
            'nazev' => $postData['nazev'],
            'obsah' => $postData['obsah'],
            'viditelnost' => isset($postData['viditelnost']) ? 1 : 0,
            'nahled_foto' => $nahledFoto,
            'user_id' => $_SESSION['user_id'],
            'autor' => 1,
            'url' => strtolower(preg_replace('/\s+/', '-', trim($postData['nazev']))),
            'datum' => date('Y-m-d H:i:s')
        ];

        $result = $this->articleModel->create($data);

        if ($result) {
            header("Location: /admin/articles");
            exit;
        } else {
            echo "Chyba při ukládání článku.";
        }
    }

    public function edit($id)
    {
        $article = $this->articleModel->getById($id); // Načtení článku podle ID
        if (!$article) {
            echo "Článek nenalezen.";
            return;
        }

        $categoryModel = new Category($this->model); // Použití modelu kategorie
        $categories = $categoryModel->getAll(); // Načtení všech kategorií

        $view = '../../app/Views/Admin/articles/edit.php';
        include '../../app/Views/Admin/layout/base.php';
    }




    // Aktualizace článku
    public function update($id, $postData)
    {
        if (empty($postData['nazev']) || empty($postData['obsah'])) {
            echo "Název a obsah článku jsou povinné.";
            return;
        }

        $targetDir = __DIR__ . '../../../uploads/thumbnails/';
        $nahledFoto = $postData['current_foto']; // Použijeme aktuální foto, pokud není nové

        // Kontrola a vytvoření složky, pokud neexistuje
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (isset($_FILES['nahled_foto']) && $_FILES['nahled_foto']['error'] === UPLOAD_ERR_OK) {
            $uniqueName = uniqid() . '-' . basename($_FILES['nahled_foto']['name']);
            $targetFile = $targetDir . $uniqueName;

            if (move_uploaded_file($_FILES['nahled_foto']['tmp_name'], $targetFile)) {
                $nahledFoto = $uniqueName;
                echo "<p>Nová fotka byla úspěšně nahrána:</p>";
                echo "<img src='/uploads/thumbnails/$nahledFoto' alt='Náhled' style='max-width: 150px;'>";
            }
        }

        $data = [
            'id' => $id,
            'nazev' => $postData['nazev'],
            'obsah' => $postData['obsah'],
            'viditelnost' => isset($postData['viditelnost']) ? 1 : 0,
            'nahled_foto' => $nahledFoto,
            'user_id' => $_SESSION['user_id'],
            'autor' => 1,
            'url' => strtolower(preg_replace('/\s+/', '-', trim($postData['nazev']))),
            'datum' => date('Y-m-d H:i:s')
        ];

        $result = $this->articleModel->update($data);

        if ($result) {
            header("Location: /admin/articles");
            exit;
        } else {
            echo "Chyba při aktualizaci článku.";
        }
    }
}
