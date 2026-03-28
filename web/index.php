<?php
// DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
// $testFile = dirname(__DIR__) . '/logs/debug_test.log';
// @file_put_contents($testFile, date('Y-m-d H:i:s') . " - web/index.php loaded - URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n", FILE_APPEND);

// Zapnutí output buffering pro zabránění problémů s headers
if (!ob_get_level()) {
    ob_start();
}

// Spuštění session na začátku
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

require '../config/db.php';
require '../config/autoloader.php';

use App\Controllers\Web\ArticleController;
use App\Controllers\Web\SearchController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\UserController;
use App\Controllers\Web\CategoryController;
use App\Controllers\Web\LinkTrackingController;
use App\Controllers\LoginController;

$db = (new Database())->connect();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Normalizace URI - odstranění koncových lomítek (kromě root "/")
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

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
        // SEO pro 404 stránku - noindex, nofollow
        $title = "Stránka nenalezena | Cyklistický magazín";
        $description = "Požadovaná stránka nebyla nalezena. Zkuste navštívit hlavní stránku nebo použít vyhledávání.";
        $robotsMeta = 'noindex, nofollow';
        $view = "../app/Views/Web/templates/404.php";
        require "../app/Views/Web/layouts/base.php";
        exit;
    }
}

// Handling pro hunspell soubory
if (preg_match('/^\/js\/hunspell\/(.+)$/', $uri, $matches)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath)) {
        // Nastavení správných hlaviček pro hunspell soubory
        if (strpos($filePath, '.aff') !== false) {
            header("Content-Type: text/plain; charset=utf-8");
        } elseif (strpos($filePath, '.dic') !== false) {
            header("Content-Type: text/plain; charset=utf-8");
        }
        header("Access-Control-Allow-Origin: *");
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        exit;
    }
}

$routes = [
    '/' => [HomeController::class, 'index'],
    '' => [HomeController::class, 'index'],
    '/category/([^/]+)' => [CategoryController::class, 'listByCategory'],
    '/article/([^/]+)' => [ArticleController::class, 'articleDetail'],
    '/categories' => [CategoryController::class, 'index'],
    '/articles' => [ArticleController::class, 'index'],
    '/authors' => [UserController::class, 'index'],
    '/login' => [LoginController::class, 'showLoginForm'],
    '/login/submit' => [LoginController::class, 'login'],
    '/logout' => [LoginController::class, 'logout'],
    '/search' => [SearchController::class, 'index'],
    '/kontakt' => [HomeController::class, 'kontakt'],
    
    // Staré race URL - 301 redirecty na /events (pro SEO a zpětnou kompatibilitu)
    '/race' => [HomeController::class, 'race'],
    '/race/cyklistickey' => [HomeController::class, 'raceCyklistickey'],
    '/race/bezeckey' => [HomeController::class, 'raceBezeckey'],
    
    // Nové events URL
    '/events' => [HomeController::class, 'events'],
    '/events/(\d+)/([^/]+)' => [HomeController::class, 'eventDetail'],
    '/appka' => [HomeController::class, 'appka'],
    '/navod' => [HomeController::class, 'manual'],
    '/obchodni-podminky' => [HomeController::class, 'obchodniPodminky'],
    '/ochrana-osobnich-udaju' => [HomeController::class, 'ochranaOsobnichUdaju'],
    '/podminky-ochrany-osobnich-udaju' => [HomeController::class, 'ochranaOsobnichUdaju'],
    '/register' => [LoginController::class, 'create'],
    '/register/submit' => [LoginController::class, 'store'],
    '/reset-password' => [LoginController::class, isset($_GET['token']) ? 'confirmResetPassword' : 'reset'],
    '/reset-password/submit' => [LoginController::class, 'resetPassword'],
    '/reset-password/link' => [LoginController::class, 'showResetLink'],
    '/reset-password/save' => [LoginController::class, 'saveNewPassword'],
    '/user/([^/]+)' => [UserController::class, 'userDetail'],
    '/user/([^/]+)/articles' => [UserController::class, 'userArticles'],
    '/track/([A-Za-z0-9_-]+)' => [LinkTrackingController::class, 'track', 'token'],
    '/sitemap.xml' => ['sitemap', 'generate'],
    '/robots.txt' => ['robots', 'generate'],
];

$routeFound = false;

// DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
// $debugFile = dirname(__DIR__) . '/logs/debug_test.log';
// @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ROUTING START - URI: $uri, METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
// @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - POST keys: " . implode(', ', array_keys($_POST ?? [])) . "\n", FILE_APPEND);

// Debug logování pro všechny requests - NA SAMÉM ZAČÁTKU
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$debugFile = dirname(__DIR__) . '/logs/debug_routing.log';
@mkdir(dirname($debugFile), 0755, true);

// ZAPÍŠEME VŠECHNY INFO NA SAMÉM ZAČÁTKU
@file_put_contents($debugFile, "\n" . str_repeat("=", 80) . "\n", FILE_APPEND);
@file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ROUTING START\n", FILE_APPEND);
@file_put_contents($debugFile, "URI: $uri\n", FILE_APPEND);
@file_put_contents($debugFile, "REQUEST_METHOD: $requestMethod\n", FILE_APPEND);
@file_put_contents($debugFile, "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n", FILE_APPEND);
@file_put_contents($debugFile, "HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'N/A') . "\n", FILE_APPEND);
@file_put_contents($debugFile, "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A') . "\n", FILE_APPEND);
@file_put_contents($debugFile, "Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'N/A') . "\n", FILE_APPEND);
@file_put_contents($debugFile, "\$_POST exists: " . (isset($_POST) ? 'YES' : 'NO') . "\n", FILE_APPEND);
@file_put_contents($debugFile, "\$_POST empty: " . (empty($_POST) ? 'YES' : 'NO') . "\n", FILE_APPEND);

if ($requestMethod === 'POST') {
    @file_put_contents($debugFile, "POST keys: " . implode(', ', array_keys($_POST ?? [])) . "\n", FILE_APPEND);
    @file_put_contents($debugFile, "POST data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);
    
    // Zkusíme přečíst raw POST data
    $rawPost = file_get_contents('php://input');
    @file_put_contents($debugFile, "Raw POST data length: " . strlen($rawPost) . "\n", FILE_APPEND);
    @file_put_contents($debugFile, "Raw POST data (first 500): " . substr($rawPost, 0, 500) . "\n", FILE_APPEND);
} else {
    @file_put_contents($debugFile, "GET keys: " . implode(', ', array_keys($_GET ?? [])) . "\n", FILE_APPEND);
    @file_put_contents($debugFile, "GET data:\n" . print_r($_GET, true) . "\n", FILE_APPEND);
}

foreach ($routes as $path => $route) {
    if (preg_match('#^' . $path . '$#', $uri, $matches)) {
        error_log("Route matched: " . $path);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Route matched: $path\n", FILE_APPEND);
        
        // Speciální handling pro sitemap a robots
        if ($route[0] === 'sitemap') {
            include 'sitemap.php';
            exit;
        }
        
        if ($route[0] === 'robots') {
            header('Content-Type: text/plain');
            readfile('robots.txt');
            exit;
        }
        
        if ($route[0] === 'migrate_db') {
            include 'migrate_db.php';
            exit;
        }
        if ($route[0] === 'migrate_images') {
            include 'migrate_images.php';
            exit;
        }
        if ($route[0] === 'migrate_audio_rename') {
            include 'migrate_audio_rename.php';
            exit;
        }
        if ($route[0] === 'migrate_audio_from_db') {
            include 'migrate_audio_from_db.php';
            exit;
        }
        
        $controllerClass = $route[0];
        $method = $route[1];
        $param = $matches[1] ?? null;

        $controller = new $controllerClass($db);

        // Speciální handling pro POST metody, které potřebují $_POST data
        $postMethods = ['login', 'store', 'resetPassword', 'saveNewPassword'];
        
        if ($method === 'login') {
            $controller->$method($_POST['email'] ?? '', $_POST['password'] ?? '');
        } else if ($method === 'store') {
            // Registrace - očekává $_POST v metodě
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - STORE METHOD called\n", FILE_APPEND);
            if ($requestMethod !== 'POST') {
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ERROR: STORE called with $requestMethod instead of POST\n", FILE_APPEND);
                http_response_code(405);
                die('Method Not Allowed - POST required');
            }
            $controller->$method();
        } else if ($method === 'resetPassword') {
            // Reset hesla - očekává $_POST v metodě
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - RESET PASSWORD METHOD called\n", FILE_APPEND);
            if ($requestMethod !== 'POST') {
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ERROR: RESET PASSWORD called with $requestMethod instead of POST\n", FILE_APPEND);
                http_response_code(405);
                die('Method Not Allowed - POST required');
            }
            $controller->$method();
        } else if ($method === 'saveNewPassword') {
            // Uložení nového hesla - očekává $_POST v metodě
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - SAVE NEW PASSWORD METHOD called\n", FILE_APPEND);
            if ($requestMethod !== 'POST') {
                @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ERROR: SAVE NEW PASSWORD called with $requestMethod instead of POST\n", FILE_APPEND);
                http_response_code(405);
                die('Method Not Allowed - POST required');
            }
            $controller->$method();
        } else if ($method === 'eventDetail' && isset($matches[1]) && isset($matches[2])) {
            // Speciální handling pro eventDetail s dvěma parametry
            $controller->$method($matches[1], $matches[2]);
        } else if ($method === 'viewLog' && isset($matches[1])) {
            // Speciální handling pro viewLog s parametrem
            $controller->$method($matches[1]);
        } else if ($method === 'track' && isset($matches[1])) {
            // Speciální handling pro track s tokenem
            $controller->$method($matches[1]);
        } else if ($param) {
            $controller->$method($param);
        } else {
            $controller->$method();
        }

        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ROUTING END - Method executed\n", FILE_APPEND);
        $routeFound = true;
        break;
    }
}

if (!$routeFound) {
    http_response_code(404);
    // SEO pro 404 stránku - noindex, nofollow
    $title = "Stránka nenalezena | Cyklistický magazín";
    $description = "Požadovaná stránka nebyla nalezena. Zkuste navštívit hlavní stránku nebo použít vyhledávání.";
    $robotsMeta = 'noindex, nofollow';
    $view = "../app/Views/Web/templates/404.php";
    require "../app/Views/Web/layouts/base.php";
    exit;
}
