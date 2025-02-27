<?php

require '../config/db.php';
require '../config/autoloader.php';

use App\Controllers\Public\ArticleController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\CategoryController;
use App\Controllers\LoginController;

$db = (new Database())->connect();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('/^\/uploads\/(.+)$/', $uri, $matches)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath)) {
        // ✅ Správné nastavení hlavičky pro zobrazení obrázků
        $mimeType = mime_content_type($filePath);
        header("Content-Type: $mimeType");
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        echo "404 - Obrázek nenalezen.";
        exit;
    }
}

// 🔀 Hlavní směrování podle dostupných metod v LoginControlleru
if ($uri === '/' || $uri === '/home') {
    $controller = new HomeController($db);
    $controller->index();
} elseif (preg_match('/\/category\/([\w-]+)/', $uri, $matches)) {
    $controller = new HomeController($db);
    $controller->listByCategory($matches[1]);
} elseif (preg_match('/\/article\/([\w-]+)/', $uri, $matches)) {
    $controller = new HomeController($db);
    $controller->articleDetail($matches[1]);
} elseif ($uri === '/categories') {
    $controller = new CategoryController($db);
    $controller->index();
} elseif ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // 🔐 Přihlášení - zobrazení formuláře
    $controller = new LoginController($db);
    $controller->showLoginForm();
} elseif ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 📝 Přihlášení - zpracování údajů
    $controller = new LoginController($db);
    $controller->login($_POST['email'], $_POST['password']);
} elseif ($uri === '/logout') {
    // 🚪 Odhlášení
    $controller = new LoginController($db);
    $controller->logout();
} elseif ($uri === '/register' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // 📝 Registrace - zobrazení formuláře
    $controller = new LoginController($db);
    $controller->create();
} elseif ($uri === '/register/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 📝 Registrace - uložení dat
    $controller = new LoginController($db);
    $controller->store();
} elseif ($uri === '/reset-password') {
    $controller = new LoginController($db);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 🔑 Pokud je token v URL, zobraz formulář pro nové heslo
        if (isset($_GET['token'])) {
            $controller->confirmResetPassword();
        } else {
            // 📝 Jinak zobraz formulář pro zadání e-mailu
            $controller->reset();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 📨 POST - generování tokenu nebo uložení nového hesla
        if (isset($_POST['token'])) {
            // 📝 Uložení nového hesla
            $controller->saveNewPassword();
        } else {
            // 📨 Generování tokenu a logování odkazu
            $controller->resetPassword();
        }
    }
} else {
    // 🛑 404 - Stránka nenalezena
    echo "404 - Stránka nenalezena. hovno";
}
