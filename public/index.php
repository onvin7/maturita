<?php
require '../config/db.php';
require '../config/autoloader.php';

use App\Controllers\Public\ArticleController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\CategoryController;
use App\Controllers\LoginController;

$db = (new Database())->connect();

$uri = $_SERVER['REQUEST_URI'];

// Public routing
if ($uri === '/' || $uri === '/home') {
    $controller = new HomeController($db);
    $controller->index();
} elseif (preg_match('/\/category\/([\w-]+)/', $uri, $matches)) {
    $controller = new HomeController($db);
    $controller->listByCategory($matches[1]); // Používáme URL místo ID
} elseif (preg_match('/\/article\/([\w-]+)/', $uri, $matches)) {
    $controller = new HomeController($db);
    $controller->articleDetail($matches[1]); // Používáme URL místo ID
} elseif ($uri === '/categories') {
    $controller = new CategoryController($db);
    $controller->index();
} elseif ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new LoginController($db);
    $controller->showLoginForm();
} elseif ($uri === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController($db);
    $controller->login($_POST['email'], $_POST['password']);
} elseif ($uri === '/logout') {
    $controller = new LoginController($db);
    $controller->logout();
} elseif ($uri === '/register' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new LoginController($db);
    $controller->create();
} elseif ($uri === '/register/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController($db);
    $controller->store();
} elseif ($uri === '/reset-password' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new LoginController($db);
    $controller->reset();
} elseif ($uri === '/reset-password/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController($db);
    $controller->resetPassword();
} else {
    echo "Stránka nenalezena.";
}
