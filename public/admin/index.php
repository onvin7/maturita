<?php
require '../../config/db.php';
require '../../config/autoloader.php';

use App\Middleware\AuthMiddleware;
use App\Controllers\Admin\HomeAdminController;
use App\Controllers\Admin\StatisticsAdminController;
use App\Controllers\Admin\AccessControlAdminController;
use App\Controllers\Admin\ArticleAdminController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\UserAdminController;
use App\Controllers\LoginController;

// Inicializace připojení k databázi
$db = (new Database())->connect();

// Middleware pro ověření přístupu
AuthMiddleware::check($db);

// Zpracování URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Získání pouze cesty bez query parametrů
$uri = str_replace('/admin', '', $uri); // Odebereme část `/admin`
$uri = trim($uri, '/'); // Odebereme zbytečná lomítka

// Definice rout s dynamickým rozpoznáním
$routes = [
    '' => [HomeAdminController::class, 'index'],
    'statistics' => [StatisticsAdminController::class, 'index'],
    'statistics/top' => [StatisticsAdminController::class, 'top'],
    'statistics/view' => [StatisticsAdminController::class, 'view', 'id'], // s dynamickým ID
    'articles' => [ArticleAdminController::class, 'index'],
    'articles/create' => [ArticleAdminController::class, 'create'],
    'articles/store' => [ArticleAdminController::class, 'store', 'data'], // Označení pro POST data
    'articles/edit' => [ArticleAdminController::class, 'edit', 'id'], // s dynamickým ID
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
    'logout' => [LoginController::class, 'logout'], // Logout přidán
];

// Dynamické zpracování rout
$routeFound = false;

foreach ($routes as $path => $route) {
    if (preg_match('#^' . $path . '(/(\d+))?$#', $uri, $matches)) {
        $controllerClass = $route[0];
        $method = $route[1];
        $param = $matches[2] ?? null; // Získání ID, pokud existuje
        $controller = new $controllerClass($db);

        // Pokud je metoda `store` nebo jiná s POST daty, použijeme `$_POST`
        if ($path === 'articles/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->$method($param, $_POST); // Přidání dat z formuláře
        } elseif ($path === 'articles/store') {
            $controller->$method($_POST);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'categories/store') {
            $controller->$method($_POST); // Předání dat z formuláře
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'categories/update') {
            $controller->$method($param, $_POST); // Předání dat z formuláře a ID
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === 'users/update') {
            $controller->$method($param, $_POST); // Předání ID a dat z formuláře
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
