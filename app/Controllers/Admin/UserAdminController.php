<?php

namespace App\Controllers\Admin;

use App\Models\User;

class UserAdminController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new User($db);
    }

    // Zobrazení seznamu uživatelů
    public function index()
    {
        $sortBy = $_GET['sort_by'] ?? 'id';      // Výchozí řazení podle ID
        $order = $_GET['order'] ?? 'ASC';       // Výchozí vzestupné řazení
        $filter = $_GET['filter'] ?? '';        // Výchozí bez filtru

        // Načtení uživatelů s filtrováním a řazením
        $users = $this->model->getAllWithSortingAndFiltering($sortBy, $order, $filter);

        // Zobrazení view
        $view = '../../app/Views/Admin/users/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }


    public function edit($id)
    {
        // Načtení uživatele z databáze
        $user = $this->model->getById($id);

        // Kontrola, zda byl uživatel nalezen
        if (!$user) {
            echo "Uživatel nenalezen.";
            return;
        }

        // Zahrnutí šablony pro úpravu uživatele
        $view = '../../app/Views/Admin/users/edit.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    public function update($id, $postData)
    {
        if (empty($postData['email']) || empty($postData['name']) || empty($postData['surname'])) {
            echo "E-mail, jméno a příjmení jsou povinné.";
            return;
        }

        $data = [
            'id' => $id,
            'email' => $postData['email'],
            'name' => $postData['name'],
            'surname' => $postData['surname'],
            'role' => $postData['role'] ?? 0,
            'profil_foto' => $postData['profil_foto'] ?? null,
            'zahlavi_foto' => $postData['zahlavi_foto'] ?? null,
            'popis' => $postData['popis'] ?? ''
        ];

        $result = $this->model->update($data);

        if ($result) {
            header("Location: /admin/users");
            exit;
        } else {
            echo "Chyba při aktualizaci uživatele.";
        }
    }
    public function delete($id)
    {
        $result = $this->model->delete($id); // Volání metody `delete` v modelu

        if ($result) {
            header("Location: /admin/users");
            exit;
        } else {
            echo "Chyba při mazání uživatele.";
        }
    }
}
