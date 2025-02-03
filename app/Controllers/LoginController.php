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
        $disableNavbar = true;

        $view = '../app/Views/login.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function login($email, $password)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $this->model->getByEmail($email);

        if (!$user) {
            echo "<script>alert('Uživatel neexistuje!'); window.location.href='/login';</script>";
            exit();
        }

        if (!password_verify($password, $user['heslo'])) {
            echo "<script>alert('Špatné heslo!'); window.location.href='/login';</script>";
            exit();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        echo "<script>window.location.href='/admin';</script>";
        exit();
    }


    // Odhlášení
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }

    // Zobrazení registračního formuláře
    public function create()
    {
        $view = '../app/Views/Admin/users/create.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Zpracování registrace
    public function store()
    {
        $data = [
            'email' => trim($_POST['email']),
            'heslo' => trim($_POST['heslo']),
            'confirm_heslo' => trim($_POST['confirm_heslo']),
            'role' => 0, // Výchozí role uživatele
            'name' => trim($_POST['name']),
            'surname' => trim($_POST['surname']),
        ];

        // Validace povinných polí
        if (empty($data['email']) || empty($data['heslo']) || empty($data['confirm_heslo']) || empty($data['name']) || empty($data['surname'])) {
            echo "<script>alert('Vyplňte všechna povinná pole.'); window.location.href='/register';</script>";
            return;
        }

        // Ověření shody hesel
        if ($data['heslo'] !== $data['confirm_heslo']) {
            echo "<script>alert('Hesla se neshodují.'); window.location.href='/register';</script>";
            return;
        }

        // Kontrola, zda e-mail již existuje
        if ($this->model->checkEmailExists($data['email'])) {
            echo "<script>alert('Účet s tímto e-mailem již existuje.'); window.location.href='/register';</script>";
            return;
        }

        // Uložení uživatele do databáze
        if ($this->model->createUser($data)) {
            echo "<script>alert('Registrace byla úspěšná.'); window.location.href='/login';</script>";
        } else {
            echo "<script>alert('Chyba při registraci. Zkuste to znovu.'); window.location.href='/register';</script>";
        }
    }

    public function reset()
    {
        $view = '../app/Views/Admin/users/reset_password.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function resetPassword()
    {
        $email = trim($_POST['email']);

        // Kontrola, zda e-mail existuje
        if (!$this->model->checkEmailExists($email)) {
            echo "<script>alert('Účet s tímto e-mailem neexistuje.'); window.location.href='/reset-password';</script>";
            return;
        }

        // Generování nového hesla
        $newPassword = bin2hex(random_bytes(4)); // 8 znakové heslo
        if ($this->model->resetUserPassword($email, $newPassword)) {
            // Odeslání hesla na e-mail
            mail($email, "Reset hesla", "Vaše nové heslo je: $newPassword");
            echo "<script>alert('Vaše nové heslo bylo odesláno na váš e-mail.'); window.location.href='/login';</script>";
        } else {
            echo "<script>alert('Chyba při resetu hesla. Zkuste to znovu.'); window.location.href='/reset-password';</script>";
        }
    }
}
