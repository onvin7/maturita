<?php
session_start();

require '../../config/db.php';
require '../../config/autoloader.php';

use App\Middleware\AuthMiddleware;
use App\Controllers\Admin\HomeAdminController;
use App\Controllers\Admin\StatisticsAdminController;
use App\Controllers\Admin\ArticleAdminController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\UserAdminController;
use App\Controllers\Admin\AccessControlAdminController;
use App\Controllers\LoginController;

// ✅ **Inicializace připojení k databázi**
$db = (new Database())->connect();

// ✅ **Middleware pro ověření přístupu**
AuthMiddleware::check($db);

// ✅ **Definice dostupných rout**
$routes = [
    'statistics' => [StatisticsAdminController::class, 'index'],
    'statistics/top' => [StatisticsAdminController::class, 'top'],
    'statistics/view' => [StatisticsAdminController::class, 'view', 'id'],
    'articles' => [ArticleAdminController::class, 'index'],
    'articles/create' => [ArticleAdminController::class, 'create'],
    'articles/store' => [ArticleAdminController::class, 'store', 'data'],
    'articles/edit' => [ArticleAdminController::class, 'edit', 'id'],
    'articles/update' => [ArticleAdminController::class, 'update', 'id'],
    'articles/delete' => [ArticleAdminController::class, 'delete', 'id'],
    'categories' => [CategoryAdminController::class, 'index'],
    'categories/create' => [CategoryAdminController::class, 'create'],
    'categories/store' => [CategoryAdminController::class, 'store'],
    'categories/edit' => [CategoryAdminController::class, 'edit', 'id'],
    'categories/update' => [CategoryAdminController::class, 'update', 'id'],
    'categories/delete' => [CategoryAdminController::class, 'delete', 'id'],
    'users' => [UserAdminController::class, 'index'],
    'users/edit' => [UserAdminController::class, 'edit', 'id'],
    'users/update' => [UserAdminController::class, 'update', 'id'],
    'users/delete' => [UserAdminController::class, 'delete', 'id'],
    'access-control' => [AccessControlAdminController::class, 'index'],
    'access-control/update' => [AccessControlAdminController::class, 'update'],
    'logout' => [LoginController::class, 'logout'],
    'upload-image' => [ArticleAdminController::class, 'uploadImage'],
];

// ✅ **Načtení přístupných rout ze session**
$accessibleRoutes = $_SESSION['accessibleRoutes'] ?? array_keys($routes);

// ✅ **Zpracování URI**
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/admin/', '', $uri);
$uri = trim($uri, '/');

// ✅ **Pokud je hlavní stránka, pustíme ji vždy**
if ($uri === '' || $uri === 'home') {
    (new HomeAdminController($db))->index();
    exit();
}

// ✅ **Dynamické zpracování rout**
$routeFound = false;

foreach ($routes as $path => $route) {
    if (preg_match('#^' . $path . '(/(\d+))?$#', $uri, $matches)) {
        $controllerClass = $route[0];
        $method = $route[1];
        $param = $matches[2] ?? null;

        // ✅ **Kontrola přístupu k dané stránce pro role 1 a 2**
        if ($accessibleRoutes !== null && !in_array($path, $accessibleRoutes)) {
            echo "<script>alert('Na tuto stránku nemáte přístup.'); window.history.back();</script>";
            $routeFound = true;
            break;
        }

        $controller = new $controllerClass($db);

        // ✅ **Zpracování metod podle HTTP požadavku**
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'articles/store') {
            $controller->$method($_POST);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'articles/update') {
            $controller->$method($param, $_POST);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'categories/store') {
            $controller->$method($_POST);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'categories/update') {
            $controller->$method($param, $_POST);
        } elseif ($param) {
            $controller->$method($param);
        } else {
            $controller->$method();
        }

        $routeFound = true;
        break;
    }
}

// ✅ **Pokud routa nebyla nalezena, vypíšeme chybu**
if (!$routeFound) {
    die("🔥 CHYBA: Stránka nenalezena -> " . $uri);
}
