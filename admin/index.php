<?php
// Spuštění session - kontrola, zda už neběží
if (session_status() === PHP_SESSION_NONE) {
    // DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
    // $possibleLogPaths = [
    //     dirname(__DIR__) . '/logs/debug_test.log',  // bicenc/logs/
    //     dirname(dirname(__DIR__)) . '/logs/debug_test.log',  // subdom/logs/
    // ];
    // $debugFile = file_exists($possibleLogPaths[1]) ? $possibleLogPaths[1] : $possibleLogPaths[0];
    
    // Zajistit, aby se používala stejná session name a cookie params
    $sessionName = session_name();
    $cookieParams = session_get_cookie_params();
    
    // DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - Cookie params before start: " . print_r($cookieParams, true) . "\n", FILE_APPEND);
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - Session name: " . $sessionName . "\n", FILE_APPEND);
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - Session save path: " . session_save_path() . "\n", FILE_APPEND);
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - All cookies: " . print_r($_COOKIE, true) . "\n", FILE_APPEND);
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - PHPSESSID cookie value: " . ($_COOKIE[$sessionName] ?? 'NOT SET') . "\n", FILE_APPEND);
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - HTTP headers: " . print_r(getallheaders(), true) . "\n", FILE_APPEND);
    
    // Zajistit, aby se používala stejná session cookie - PŘED session_start()
    // Použijeme stejné parametry jako v login
    session_set_cookie_params(
        $cookieParams['lifetime'] ?: 0,
        $cookieParams['path'] ?: '/',
        $cookieParams['domain'] ?: '',
        $cookieParams['secure'] ?: false,
        $cookieParams['httponly'] ?: true
    );
    
    // Session by měla fungovat přes cookie normálně
    session_start();
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - Session started, ID: " . session_id() . "\n", FILE_APPEND);
    // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - ADMIN INDEX - Session data after start: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
}

// Kontrola, zda není požadavek z /web/admin
if (strpos($_SERVER['REQUEST_URI'], '/web/admin') === 0) {
    $newUri = str_replace('/web/admin', '/admin', $_SERVER['REQUEST_URI']);
    header("Location: " . $newUri);
    exit;
}

require '../config/db.php';
require '../config/autoloader.php';

use App\Middleware\AuthMiddleware;
use App\Controllers\Admin\HomeAdminController;
use App\Controllers\Admin\StatisticsAdminController;
use App\Controllers\Admin\ArticleAdminController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\UserAdminController;
use App\Controllers\Admin\AccessControlAdminController;
use App\Controllers\Admin\PromotionAdminController;
use App\Controllers\Admin\AdAdminController;
use App\Controllers\Admin\FlashNewsJSONAdminController;
use App\Controllers\Admin\LinkClicksAdminController;
use App\Controllers\Admin\LogsAdminController;
use App\Controllers\LoginController;

// ✅ **Inicializace připojení k databázi**
$db = (new Database())->connect();

// ✅ **Handling pro hunspell soubory**
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

if (preg_match('/^\/js\/hunspell\/(.+)$/', $uri, $matches)) {
    $filePath = __DIR__ . '/../web' . $uri;
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

// ✅ **Middleware pro ověření přístupu**
AuthMiddleware::check($db);

// ✅ **Definice dostupných rout**
$routes = [
    'statistics' => [StatisticsAdminController::class, 'index'],
    'statistics/articles' => [StatisticsAdminController::class, 'articles'],
    'statistics/categories' => [StatisticsAdminController::class, 'categories'],
    'statistics/authors' => [StatisticsAdminController::class, 'authors'],
    'statistics/performance' => [StatisticsAdminController::class, 'performance'],
    'statistics/views' => [StatisticsAdminController::class, 'views'],
    'statistics/clicks' => [StatisticsAdminController::class, 'clicks'],
    'statistics/top' => [StatisticsAdminController::class, 'top'],
    'statistics/view' => [StatisticsAdminController::class, 'view', 'id'],
    'statistics/article-details/(\d+)' => [StatisticsAdminController::class, 'getArticleDetails', 'articleId'],
    'statistics/category-details/(\d+)' => [StatisticsAdminController::class, 'getCategoryDetails', 'categoryId'],
    'statistics/author-details/(\d+)' => [StatisticsAdminController::class, 'getAuthorDetails', 'authorId'],
    'articles' => [ArticleAdminController::class, 'index'],
    'articles/create' => [ArticleAdminController::class, 'create'],
    'articles/store' => [ArticleAdminController::class, 'store', 'data'],
    'articles/edit/(\d+)' => [ArticleAdminController::class, 'edit', 'id'],
    'articles/update/(\d+)' => [ArticleAdminController::class, 'update', 'id'],
    'articles/delete/(\d+)' => [ArticleAdminController::class, 'delete', 'id'],
    'articles/preview/(\d+)' => [ArticleAdminController::class, 'preview', 'id'],
    'categories' => [CategoryAdminController::class, 'index'],
    'categories/create' => [CategoryAdminController::class, 'create'],
    'categories/store' => [CategoryAdminController::class, 'store'],
    'categories/edit/(\d+)' => [CategoryAdminController::class, 'edit', 'id'],
    'categories/update/(\d+)' => [CategoryAdminController::class, 'update', 'id'],
    'categories/delete/(\d+)' => [CategoryAdminController::class, 'delete', 'id'],
    'users' => [UserAdminController::class, 'index'],
    'users/edit/(\d+)' => [UserAdminController::class, 'edit', 'id'],
    'users/update/(\d+)' => [UserAdminController::class, 'update', 'id'],
    'users/delete/(\d+)' => [UserAdminController::class, 'delete', 'id'],
    'access-control' => [AccessControlAdminController::class, 'index'],
    'access-control/update' => [AccessControlAdminController::class, 'update'],
    'logout' => [LoginController::class, 'logout'],
    'upload-image' => [ArticleAdminController::class, 'uploadImage'],
    'promotions' => [PromotionAdminController::class, 'index'],
    'promotions/create' => [PromotionAdminController::class, 'create'],
    'promotions/store' => [PromotionAdminController::class, 'store'],
    'promotions/upcoming' => [PromotionAdminController::class, 'upcoming'],
    'promotions/history' => [PromotionAdminController::class, 'history'],
    'promotions/delete/(\d+)' => [PromotionAdminController::class, 'delete', 'id'],
    'ads' => [AdAdminController::class, 'index'],
    'ads/create' => [AdAdminController::class, 'create'],
    'ads/store' => [AdAdminController::class, 'store'],
    'ads/edit/(\d+)' => [AdAdminController::class, 'edit', 'id'],
    'ads/update/(\d+)' => [AdAdminController::class, 'update', 'id'],
    'ads/delete/(\d+)' => [AdAdminController::class, 'delete', 'id'],
    'ads/toggle-active/(\d+)' => [AdAdminController::class, 'toggleActive', 'id'],
    'ads/set-default/(\d+)' => [AdAdminController::class, 'setDefault', 'id'],
    'settings' => [UserAdminController::class, 'settings'],
    'settings/update' => [UserAdminController::class, 'updateSettings'],
    'social-sites' => [UserAdminController::class, 'socialSites'],
    'social-sites/save' => [UserAdminController::class, 'saveSocialSite'],
    'social-sites/delete/(\d+)' => [UserAdminController::class, 'deleteSocialSite', 'id'],
    'flashnews' => [FlashNewsJSONAdminController::class, 'index'],
    'flashnews/create' => [FlashNewsJSONAdminController::class, 'create'],
    'flashnews/store' => [FlashNewsJSONAdminController::class, 'store'],
    'flashnews/edit' => [FlashNewsJSONAdminController::class, 'edit'],
    'flashnews/update' => [FlashNewsJSONAdminController::class, 'update'],
    'flashnews/delete' => [FlashNewsJSONAdminController::class, 'delete'],
    'flashnews/toggle-active' => [FlashNewsJSONAdminController::class, 'toggleActive'],
    'flashnews/update-sort-order' => [FlashNewsJSONAdminController::class, 'updateSortOrder'],
    'flashnews/reorder' => [FlashNewsJSONAdminController::class, 'reorder'],
    'flashnews/refresh' => [FlashNewsJSONAdminController::class, 'refresh'],
    'link-clicks/url/(\d+)' => [LinkClicksAdminController::class, 'urlDetails', 'linkClickId'],
    'link-clicks/article/(\d+)' => [LinkClicksAdminController::class, 'article', 'articleId'],
    'link-clicks' => [LinkClicksAdminController::class, 'index'],
    'logs/view/([a-zA-Z0-9_-]+\.log)' => [LogsAdminController::class, 'view', 'logFileName'],
    'logs' => [LogsAdminController::class, 'index'],
];

// ✅ **Načtení přístupných rout ze session**
$accessibleRoutes = $_SESSION['accessibleRoutes'] ?? array_keys($routes);

// ✅ **Zpracování URI**
$fullUri = $_SERVER['REQUEST_URI'];

// Odstranění query stringu, pokud existuje
$uri = parse_url($fullUri, PHP_URL_PATH);

// Odstranění domény a /admin/ z URI
if (preg_match('#^/[^/]+/admin/(.*)#', $uri, $matches)) {
    $uri = $matches[1];
} else {
    $uri = str_replace('/admin/', '', $uri);
}

$uri = trim($uri, '/');

// ✅ **Pokud je hlavní stránka, pustíme ji vždy**
if ($uri === '' || $uri === 'home') {
    (new HomeAdminController($db))->index();
    exit();
}

// ✅ **Dynamické zpracování rout**
$routeFound = false;

// Rozdělíme URL na segmenty
$uriSegments = explode('/', $uri);
$firstSegment = $uriSegments[0] ?? '';

// Seřadíme routy tak, aby ty s parametry (obsahující regulární výrazy) byly až na konci,
// a specifické routy (jako articles/create) byly před obecnými (articles/edit/...)
uksort($routes, function($a, $b) {
    // Pokud $a je specifická podrouta $b (např. 'articles/create' vs 'articles'), $a má přednost
    if (strpos($a, $b . '/') === 0) {
        return -1;
    }
    // Pokud $b je specifická podrouta $a, $b má přednost
    if (strpos($b, $a . '/') === 0) {
        return 1;
    }
    
    // Routy bez parametrů (bez závorek) mají přednost před routami s parametry
    $aHasParams = strpos($a, '(') !== false;
    $bHasParams = strpos($b, '(') !== false;
    
    if ($aHasParams && !$bHasParams) return 1;
    if (!$aHasParams && $bHasParams) return -1;
    
    // Jinak zachováme původní pořadí (nebo abecedně, to je jedno)
    return 0;
});

// Projdeme routy
foreach ($routes as $path => $route) {
    // Vytvoření regulárního výrazu z cesty routy
    // Nahradíme definované parametry za odpovídající regex
    // Ošetříme lomítka
    
    if (strpos($path, '(') !== false) {
        // Cesta obsahuje regulární výrazy (parametry)
        $pattern = '#^' . str_replace('/', '\/', $path) . '$#';
    } else {
        // Cesta je statická
        $pattern = '#^' . preg_quote($path, '#') . '$#';
    }
    
    if (preg_match($pattern, $uri, $matches)) {
        $controllerClass = $route[0];
        $method = $route[1];
        
        // Vytvoření instance controlleru
        $controller = new $controllerClass($db);
        
        // Získání parametrů z URL (odstraníme první prvek - celou shodu)
        array_shift($matches);
        $params = $matches;
        
        // Pokud je POST požadavek a metoda očekává data, přidáme je k parametrům
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Zjistíme, kolik parametrů metoda očekává
            $reflection = new ReflectionMethod($controllerClass, $method);
            $paramCount = $reflection->getNumberOfParameters();
            
            // Pokud máme méně parametrů z URL než metoda očekává, předáme i POST data
            // Toto řeší případy jako update($id, $data) vs store($data)
            if (count($params) < $paramCount) {
                $params[] = $_POST;
            }
        }
        
        // Zavoláme metodu s parametry
        call_user_func_array([$controller, $method], $params);
        
        $routeFound = true;
        break;
    }
}

// ✅ **Pokud routa nebyla nalezena, vypíšeme chybu s více informacemi**
if (!$routeFound) {
    echo "Err: Stránka nenalezena -> " . $uri . "<br>";
    echo "Debug info:<br>";
    echo "Původní URL: " . $fullUri . "<br>";
    echo "Zpracované URI: " . $uri . "<br>";
    echo "HTTP Metoda: " . $_SERVER['REQUEST_METHOD'] . "<br>";
    echo "Dostupné routy: " . implode(', ', array_keys($routes)) . "<br>";
    exit();
}
