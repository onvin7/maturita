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
        $roles = $this->model->getAllRoles();
        $view = '../../app/Views/Admin/access_control/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    // Aktualizace přístupů
    public function update($data)
    {
        foreach ($data['access'] as $page => $role) {
            $this->model->updateRole($page, $role);
        }
        header('Location: /admin/access-control');
        exit();
    }
}
