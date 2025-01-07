<?php

namespace App\Controllers;

use App\Models\User;

class LoginController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new User($db);
    }

    // Zobrazení přihlašovacího formuláře
    public function showLoginForm()
    {
        //echo password_hash('', PASSWORD_DEFAULT);

        include __DIR__ . '/../Views/login.php';
    }

    public function login($email, $password)
    {
        $user = $this->model->getByEmail($email);

        if ($user && password_verify($password, $user['heslo'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Přesměrování do admin panelu
            header('Location: /admin');
            exit();
        } else {
            echo "Špatné přihlašovací údaje.";
        }
    }



    // Odhlášení
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }
}
