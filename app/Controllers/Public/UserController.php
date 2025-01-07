<?php

namespace App\Controllers\Public;

use App\Models\User;

class UserController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new User($db);
    }

    // Registrace uživatele
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST['email'], $_POST['heslo'], $_POST['role'], $_POST['name'], $_POST['surname']);
            header('Location: /login');
        } else {
            include '../../app/Views/Public/users/register.php';
        }
    }

    // Přihlášení uživatele
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->model->authenticate($_POST['email'], $_POST['heslo']);
            if ($user) {
                session_start();
                $_SESSION['user'] = $user;
                header('Location: /');
            } else {
                $error = "Špatný e-mail nebo heslo";
                include '../../app/Views/Public/users/login.php';
            }
        } else {
            include '../../app/Views/Public/users/login.php';
        }
    }

    // Odhlášení uživatele
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /');
    }
}
