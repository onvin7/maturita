<?php

namespace App\Controllers\Admin;

use App\Models\AccessControl;

class AccessControlAdminController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new AccessControl($db);
    }

    // Zobrazení přístupů
    public function index()
    {
        $pages = $this->model->getAllPages();
        $accessibleLinks = $this->getAccessibleSections($_SESSION['role'] ?? 0); // Načteme dostupné sekce

        $view = '../../app/Views/Admin/access_control/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }


    // Aktualizace přístupů
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pages = $this->model->getAllPages();

            foreach ($pages as $page) {
                $pageName = $page['page'];
                $role1 = isset($_POST['role_1'][$pageName]) ? 1 : 0;
                $role2 = isset($_POST['role_2'][$pageName]) ? 1 : 0;

                if ($role1 != $page['role_1'] || $role2 != $page['role_2']) {
                    // Aktualizace v databázi
                    $this->model->updatePagePermissions($pageName, $role1, $role2);

                    // Logování změny
                    $this->model->logChange($_SESSION['user_id'], $pageName, $role1, $role2);
                }
            }

            // Přesměrování zpět na stránku
            header('Location: /admin/access-control');
            exit();
        }
    }

    // Získá seznam přístupných sekcí podle role
    public function getAccessibleSections($role)
    {
        return $this->model->getAccessibleSections($role); // Oprava názvu metody
    }

    public function getNavbarSections()
    {
        session_start();

        $role = $_SESSION['role'] ?? 0;
        return $this->model->getAccessibleSections($role);
    }
}
