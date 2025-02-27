<?php

namespace App\Controllers;

use App\Models\User;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

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
        $disableNavbar = true;
        $view = '../app/Views/login.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Přihlášení uživatele
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
        $_SESSION['email'] = $user['email'];

        echo "<script>window.location.href='/admin';</script>";
        exit();
    }

    // Odhlášení uživatele
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

    // Zobrazení formuláře pro reset hesla
    public function reset()
    {
        $view = '../app/Views/Admin/users/reset_password.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Uloží token a zapíše do logu
    public function resetPassword()
    {
        $email = trim($_POST['email']);
        $user = $this->model->getByEmail($email);

        if (!$user) {
            echo "<script>alert('Účet s tímto e-mailem neexistuje.'); window.location.href='/reset-password';</script>";
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if ($this->model->storeResetToken($user['id'], $email, $token, $expiresAt)) {
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token;

            // ✅ Cesta k JSON logu
            $logFile = __DIR__ . '/../../email_log.json';

            // ✅ Nový záznam
            $logEntry = [
                'datum' => date('Y-m-d H:i:s'),
                'email' => $email,
                'token' => $token,
                'expires_at' => $expiresAt,
                'reset_link' => $resetLink,
                'popis' => 'Odeslání odkazu pro reset hesla'
            ];

            // ✅ Načtení stávajících dat, pokud existují
            $existingLogs = [];
            if (file_exists($logFile)) {
                $existingContent = file_get_contents($logFile);
                $existingLogs = json_decode($existingContent, true) ?? [];
            }

            // ✅ Přidání nového záznamu do pole
            $existingLogs[] = $logEntry;

            // ✅ Zápis zpět do souboru v krásném formátu a s podporou češtiny
            file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            echo "<script>alert('Odkaz pro reset hesla uložen do logu.'); window.location.href='/login';</script>";
        } else {
            echo "<script>alert('Chyba při generování odkazu.'); window.location.href='/reset-password';</script>";
        }
    }

    // Potvrdí reset hesla
    public function confirmResetPassword()
    {
        $token = $_GET['token'] ?? null;
        if (!$token) {
            echo "<script>alert('Token nebyl poskytnut.'); window.location.href='/login';</script>";
            return;
        }

        $resetData = $this->model->getValidResetToken($token);
        if (!$resetData) {
            echo "<script>alert('Token je neplatný nebo expirovaný.'); window.location.href='/reset-password';</script>";
            return;
        }

        $view = '../app/Views/Admin/users/new_password.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function saveNewPassword()
    {
        $token = $_POST['token'] ?? null;
        $newPassword = $_POST['new_password'] ?? null;
        $confirmPassword = $_POST['confirm_password'] ?? null;

        if (!$token || !$newPassword || !$confirmPassword) {
            echo "<script>alert('Chybí token nebo heslo.'); window.location.href='/reset-password';</script>";
            return;
        }

        if ($newPassword !== $confirmPassword) {
            echo "<script>alert('Hesla se neshodují.'); window.history.back();</script>";
            return;
        }

        $resetData = $this->model->getValidResetToken($token);
        if (!$resetData) {
            echo "<script>alert('Token je neplatný nebo expirovaný.'); window.location.href='/reset-password';</script>";
            return;
        }

        // ✅ Kontrola, zda e-mail opravdu existuje v DB
        $user = $this->model->getByEmail($resetData['email']);
        if (!$user) {
            echo "<script>alert('Účet s tímto e-mailem neexistuje.'); window.location.href='/reset-password';</script>";
            return;
        }

        // ✅ Aktualizace hesla podle user_id
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($this->model->updatePassword($user['id'], $hashedPassword)) {
            $this->model->deleteResetToken($token);
            echo "<script>alert('Heslo bylo úspěšně změněno.'); window.location.href='/login';</script>";
        } else {
            echo "<script>alert('Chyba při změně hesla.'); window.location.href='/reset-password';</script>";
        }
    }
}
