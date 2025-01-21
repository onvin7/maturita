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

        // Pokud uživatel není přihlášen, přesměruj na login
        if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
            if ($_SERVER['REQUEST_URI'] !== '/login') { // Zabránění cyklickému přesměrování
                header('Location: /login');
                exit();
            }
        }

        // Získání URI stránky
        $uri = str_replace('/admin/', '', $_SERVER['REQUEST_URI']);
        $uri = trim($uri, '/');

        // Dynamická kontrola přístupu na základě role
        $accessControl = new AccessControl($db);

        // Role aktuálního uživatele
        $currentRole = $_SESSION['role'] ?? 0;

        // Role 0 nemá přístup nikam
        if ($currentRole === 0) {
            http_response_code(403);
            echo "Nemáte oprávnění k přístupu do administrace.";
            exit();
        }

        // Role 3 má přístup všude
        if ($currentRole === 3) {
            // Superadmin může vše, žádná další kontrola není nutná
            return;
        }

        // Načtení oprávnění z tabulky admin_access
        $pagePermissions = $accessControl->getPagePermissions($uri);

        // Pokud stránka není definovaná, nastaví výchozí oprávnění
        if (!$pagePermissions) {
            $accessControl->addPage($uri, 1, 1); // Výchozí: přístup pro role 1 a 2
            $pagePermissions = ['role_1' => 1, 'role_2' => 1];
        }

        // Kontrola oprávnění na základě role
        if (
            ($currentRole === 1 && !$pagePermissions['role_1']) ||
            ($currentRole === 2 && !$pagePermissions['role_2'])
        ) {
            http_response_code(403);
            echo "Nemáte oprávnění k přístupu na tuto stránku.";
            exit();
        }
    }
}
