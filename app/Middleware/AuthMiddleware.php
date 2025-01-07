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

        // Získání aktuální URI
        $uri = str_replace('/admin/', '', $_SERVER['REQUEST_URI']);
        $uri = trim($uri, '/');

        // Dynamická kontrola přístupu na základě role
        $accessControl = new AccessControl($db);
        $requiredRole = $accessControl->getRequiredRole($uri);

        if ($requiredRole === null) {
            $accessControl->addPage($uri, 1); // Výchozí role = 1
            $requiredRole = 1;
        }

        // Pokud role není dostatečná, zobraz chybu
        if ($_SESSION['role'] < $requiredRole) {
            http_response_code(403);
            echo "Nemáte oprávnění k přístupu na tuto stránku.";
            exit();
        }
    }
}
