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

        // ✅ **Debug: Výpis session**
        error_log("DEBUG: User ID: " . ($_SESSION['user_id'] ?? 'N/A') . ", Role: " . ($_SESSION['role'] ?? 'N/A'));

        // ✅ **Pokud uživatel není přihlášen, přesměruj na login**
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            echo "<script>alert('Musíte se přihlásit.'); window.location.href='/login';</script>";
            exit();
        }

        // ✅ **Získání aktuálního URI a odstranění parametrů**
        $uri = str_replace('/admin/', '', $_SERVER['REQUEST_URI']);
        $uri = strtok($uri, '?'); // Odstranění GET parametrů
        $uri = trim($uri, '/');

        // ✅ **Debug: Výpis aktuální URL**
        error_log("DEBUG: Přístup na URI: " . $uri);

        // ✅ **Superadmin (role 3) má neomezený přístup – ukončí kontrolu**
        $currentRole = (int) $_SESSION['role'];
        if ($currentRole === 3) {
            error_log("DEBUG: Role 3 má přístup ke všemu.");
            return;
        }

        // ✅ **Všichni přihlášení uživatelé mohou na hlavní stránku adminu**
        if ($uri === '' || $uri === 'home') {
            error_log("DEBUG: Přístup na hlavní admin stránku povolen.");
            return;
        }

        // ✅ **Načtení oprávnění z databáze**
        $accessControl = new AccessControl($db);
        $pagePermissions = $accessControl->getPagePermissions($uri);

        // ✅ **Debug: Výpis oprávnění ke stránce**
        error_log("DEBUG: Oprávnění k '$uri': " . print_r($pagePermissions, true));

        // ✅ **Pokud stránka není v databázi, přístup je zakázán**
        if (!$pagePermissions) {
            echo "<script>alert('Stránka nenalezena nebo nemáte oprávnění k přístupu.'); window.location.href='/admin';</script>";
            exit();
        }

        // ✅ **Ověření přístupů podle role**
        if (($currentRole === 1 && !$pagePermissions['role_1']) ||
            ($currentRole === 2 && !$pagePermissions['role_2'])
        ) {
            echo "<script>alert('Na tuto stránku nemáte přístup!'); window.location.href='/admin';</script>";
            exit();
        }
    }
}
