<?php
require '../../config/db.php';
require '../../config/autoloader.php';

use App\Middleware\AuthMiddleware;
use App\Models\AccessControl;
use App\Controllers\Admin\AccessControlAdminController;
use App\Controllers\Admin\HomeAdminController;
use App\Controllers\Admin\StatisticsAdminController;
use App\Controllers\Admin\ArticleAdminController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\UserAdminController;
use App\Controllers\LoginController;

// Inicializace připojení k databázi
$db = (new Database())->connect();

// Middleware pro ověření přístupu
AuthMiddleware::check($db);

// Načtení modelu AccessControl
$accessControl = new AccessControl($db);
$currentRole = $_SESSION['role'] ?? 0;

// Získání seznamu přístupných sekcí pro navbar
$accessibleSections = $accessControl->getAccessibleSections($currentRole);

// Zpracování URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/admin', '', $uri);
$uri = trim($uri, '/');

// Umožnění přístupu na home (admin/) všem rolím
if ($uri === '' || $uri === 'home') {
    $controller = new HomeAdminController($db);
    $controller->index();
    exit();
}

// Definice rout s dynamickým rozpoznáním
$routes = [
    '' => [HomeAdminController::class, 'index'],
    'statistics' => [StatisticsAdminController::class, 'index'],
    'statistics/top' => [StatisticsAdminController::class, 'top'],
    'statistics/view' => [StatisticsAdminController::class, 'view', 'id'],
    'articles' => [ArticleAdminController::class, 'index'],
    'articles/create' => [ArticleAdminController::class, 'create'],
    'articles/store' => [ArticleAdminController::class, 'store', 'data'],
    'articles/edit' => [ArticleAdminController::class, 'edit', 'id'],
    'articles/update' => [ArticleAdminController::class, 'update', 'id'],
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
];

// Dynamické zpracování rout
$routeFound = false;

foreach ($routes as $path => $route) {
    if (preg_match('#^' . $path . '(/(\d+))?$#', $uri, $matches)) {
        $controllerClass = $route[0];
        $method = $route[1];
        $param = $matches[2] ?? null;

        // Kontrola přístupu k dané stránce
        if (!in_array($path, $accessibleSections)) {
            echo "<script>alert('Na tuto stránku nemáte přístup.');</script>";
            $routeFound = true;
            break;
        }

        $controller = new $controllerClass($db);

        // Zpracování metod
        if ($path === 'articles/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->$method($_POST);
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

if (!$routeFound) {
    echo "Stránka nenalezena.";
}
