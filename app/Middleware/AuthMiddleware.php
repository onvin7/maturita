<?php

namespace App\Middleware;

use App\Models\AccessControl;
use App\Helpers\LogHelper;

class AuthMiddleware
{
    public static function check($db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Zajistit, aby se používala stejná session cookie
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params(
                $cookieParams['lifetime'],
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure'],
                $cookieParams['httponly']
            );
            session_start();
        }

        // ✅ **Debug: Výpis session** - DEBUG LOGY ZAKOMENTOVÁNY
        // $possibleLogPaths = [
        //     dirname(dirname(dirname(__DIR__))) . '/logs/debug_test.log',  // bicenc/logs/
        //     dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/debug_test.log',  // subdom/logs/
        // ];
        // $debugFile = file_exists($possibleLogPaths[1]) ? $possibleLogPaths[1] : $possibleLogPaths[0];
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - AUTH MIDDLEWARE - Session ID: " . session_id() . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - AUTH MIDDLEWARE - User ID: " . ($_SESSION['user_id'] ?? 'N/A') . ", Role: " . ($_SESSION['role'] ?? 'N/A') . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - AUTH MIDDLEWARE - All session keys: " . implode(', ', array_keys($_SESSION ?? [])) . "\n", FILE_APPEND);

        // ✅ **Pokud uživatel není přihlášen, přesměruj na login**
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - AUTH MIDDLEWARE - REDIRECTING TO LOGIN (no user_id or role)\n", FILE_APPEND);
            @LogHelper::write('security.log', 'Unauthorized access attempt - URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown') . ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' - User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
            header('Location: /login');
            exit();
        }

        // ✅ **Kontrola role - uživatelé s rolí 0 nemají přístup do administrace**
        if ((int)$_SESSION['role'] <= 0) {
            @LogHelper::write('security.log', 'Access denied - User ID: ' . ($_SESSION['user_id'] ?? 'unknown') . ', Role: ' . ($_SESSION['role'] ?? 'unknown') . ', URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown') . ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            session_destroy();
            $error_message = "Nemáte oprávnění pro přístup do administrace.";
            $view = '../app/Views/Admin/layout/access_denied.php';
            include '../app/Views/Admin/layout/base.php';
            exit();
        }

        // ✅ **Získání aktuálního URI a odstranění parametrů**
        $uri = str_replace('/admin/', '', $_SERVER['REQUEST_URI']);
        $uri = strtok($uri, '?'); // Odstranění GET parametrů
        $uri = trim($uri, '/');
        
        // ✅ **Odstranění ID z konce URL (např. articles/preview/1083 -> articles/preview)**
        // Pokud je poslední část URI číslo, odstraníme ji, aby se práva vztahovala na obecnou sekci
        $parts = explode('/', $uri);
        if (count($parts) > 1 && is_numeric(end($parts))) {
            array_pop($parts);
            $uri = implode('/', $parts);
        }

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

        // ✅ **Pokud stránka není v databázi, automaticky ji přidej**
        if (!$pagePermissions) {
            // Přidáme stránku do DB s výchozím nastavením (zakázáno pro role 1 a 2)
            // Nezáleží na tom, kdo stránku otevřel (admin nebo jiná role) - vždy se přidá
            $accessControl->addPage($uri, 0, 0);
            
            // Zalogujeme vytvoření nové stránky
            @LogHelper::write('access_control.log', 'New page auto-registered: ' . $uri . ' by User ID: ' . ($_SESSION['user_id'] ?? 'unknown'));
            
            // Pokud je to admin (role 3), pustíme ho dál (má přístup ke všemu)
            if ($currentRole === 3) {
                return;
            }
            
            // Pokud je to jiná role (1 nebo 2), musíme znovu načíst oprávnění z DB
            // (která jsme právě vytvořili jako 0, 0)
            $pagePermissions = $accessControl->getPagePermissions($uri);
            
            // Logika níže (řádky 91+) pak uživatele správně vyhodí, protože oprávnění jsou 0
        }

        // ✅ **Ověření přístupů podle role**
        if (($currentRole === 1 && !$pagePermissions['role_1']) ||
            ($currentRole === 2 && !$pagePermissions['role_2'])
        ) {
            @LogHelper::write('security.log', 'Role-based access denied - User ID: ' . ($_SESSION['user_id'] ?? 'unknown') . ', Role: ' . $currentRole . ', URI: ' . $uri . ' - IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $view = '../app/Views/Admin/layout/access_denied.php';
            include '../app/Views/Admin/layout/base.php';
            exit();
        }
    }
}
