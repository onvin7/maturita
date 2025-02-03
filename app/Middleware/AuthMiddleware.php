<?php

namespace App\Middleware;

use App\Models\AccessControl;

class AuthMiddleware
{
    public static function check($db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
            echo "<script>alert('Musíte se přihlásit.'); window.location.href='/login';</script>";
            exit();
        }

        $uri = str_replace('/admin/', '', $_SERVER['REQUEST_URI']);
        $uri = strtok($uri, '?'); // Odstraní parametry

        // Role 3 má přístup všude
        if ($_SESSION['role'] == 3) {
            return;
        }

        // Kontrola přístupů z databáze
        $accessControl = new AccessControl($db);
        $pagePermissions = $accessControl->getPagePermissions($uri);

        if (!$pagePermissions) {
            echo "<script>alert('Stránka nenalezena nebo nemáte oprávnění.'); window.history.back();</script>";
            exit();
        }

        // Kontrola přístupových práv pro role 1 a 2
        if ($_SESSION['role'] == 1 && !$pagePermissions['role_1']) {
            echo "<script>alert('Nemáte oprávnění pro tuto stránku.'); window.history.back();</script>";
            exit();
        }
        if ($_SESSION['role'] == 2 && !$pagePermissions['role_2']) {
            echo "<script>alert('Nemáte oprávnění pro tuto stránku.'); window.history.back();</script>";
            exit();
        }
    }
}
