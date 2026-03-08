<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\CsrfHelper;
use App\Helpers\LogHelper;

class LoginController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new User($db);
    }

    // Zobrazení přihlašovacího formuláře
    public function showLoginForm()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Pokud je uživatel již přihlášený, přesměruj ho do adminu
        if (isset($_SESSION['user_id'])) {
            echo "<script>window.location.href='/admin';</script>";
            exit();
        }

        $disableNavbar = true;
        $disableBootstrap = true;
        $css = ['login'];
        $adminTitle = "Přihlášení | Admin Panel - Cyklistickey magazín";
        $view = '../app/Views/login.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Přihlášení uživatele
    public function login($email, $password)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF kontrola pro login
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Neplatný bezpečnostní token (CSRF). Zkuste to prosím znovu.';
            header('Location: /login');
            exit();
        }

        // LOGIN LOG - speciální log soubor pro login
        $loginLogFile = dirname(dirname(dirname(__DIR__))) . '/logs/login.log';
        $logDir = dirname($loginLogFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        // Zapíšeme do login logu
        $logEntry = "\n" . str_repeat("=", 80) . "\n";
        $logEntry .= date('Y-m-d H:i:s') . " - LOGIN ATTEMPT\n";
        $logEntry .= str_repeat("-", 80) . "\n";
        $logEntry .= "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
        $logEntry .= "Email parameter: " . ($email ?? 'NULL') . " (length: " . strlen($email ?? '') . ")\n";
        $logEntry .= "Password parameter: " . (!empty($password) ? 'SET (length: ' . strlen($password) . ')' : 'EMPTY') . "\n";
        $logEntry .= "\nPOST data:\n" . print_r($_POST, true) . "\n";
        $logEntry .= "\nGET data:\n" . print_r($_GET, true) . "\n";
        $logEntry .= "\nSERVER variables:\n";
        $logEntry .= "  REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        $logEntry .= "  HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'N/A') . "\n";
        $logEntry .= "  REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "\n";
        $logEntry .= "  HTTP_USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "\n";
        
        // DEBUG LOGY - aktivováno pro debugging
        $possibleLogPaths = [
            dirname(dirname(dirname(__DIR__))) . '/logs/debug_test.log',  // bicenc/logs/
        ];
        
        $debugFile = null;
        foreach ($possibleLogPaths as $path) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (is_writable($dir) || is_writable($path)) {
                $debugFile = $path;
                break;
            }
        }
        
        if (!$debugFile) {
            $debugFile = $possibleLogPaths[0];
            @mkdir(dirname($debugFile), 0755, true);
        }
        
        // Zapíšeme do obou logů
        @file_put_contents($loginLogFile, $logEntry, FILE_APPEND);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN METHOD CALLED - Email: " . ($email ?? 'NULL') . ", Password: " . (!empty($password) ? 'SET (length: ' . strlen($password) . ')' : 'EMPTY') . "\n", FILE_APPEND);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Debug file path: " . $debugFile . "\n", FILE_APPEND);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Login log path: " . $loginLogFile . "\n", FILE_APPEND);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - __DIR__: " . __DIR__ . "\n", FILE_APPEND);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n", FILE_APPEND);
        
        if (session_status() === PHP_SESSION_NONE) {
            // Zajistit, aby se používala stejná session cookie - PŘED session_start()
            $cookieParams = session_get_cookie_params();
            session_set_cookie_params(
                $cookieParams['lifetime'],
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure'],
                $cookieParams['httponly']
            );
            session_start();
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Session started, ID: " . session_id() . "\n", FILE_APPEND);
        }
        else {
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Session already started, ID: " . session_id() . "\n", FILE_APPEND);
        }

        // Kontrola CSRF tokenu - dočasně vypnuto pro debug
        // if (!CSRFHelper::checkPostToken()) {
        //     $_SESSION['login_error'] = 'CSRF token validation failed!';
        //     header('Location: /login');
        //     exit();
        // }

        // Trim hodnoty před kontrolou
        $email = trim($email ?? '');
        $password = trim($password ?? '');

        // Zkontrolujeme, jestli jsou prázdné a zapíšeme do logu
        $logEntry = "";
        if (empty($email) || empty($password)) {
            $logEntry .= "ERROR: Email or password is empty!\n";
            $logEntry .= "  Email empty: " . (empty($email) ? 'YES' : 'NO') . "\n";
            $logEntry .= "  Password empty: " . (empty($password) ? 'YES' : 'NO') . "\n";
            $logEntry .= "  Email value: '" . ($email ?? 'NULL') . "'\n";
            $logEntry .= "  Password length: " . strlen($password ?? '') . "\n";
            $logEntry .= "  Checking POST directly:\n";
            $logEntry .= "    \$_POST['email']: " . (isset($_POST['email']) ? "'" . $_POST['email'] . "'" : 'NOT SET') . "\n";
            $logEntry .= "    \$_POST['password']: " . (isset($_POST['password']) ? 'SET (length: ' . strlen($_POST['password']) . ')' : 'NOT SET') . "\n";
            @file_put_contents($loginLogFile, $logEntry, FILE_APPEND);
            @LogHelper::login("Login failed - Empty email or password - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $_SESSION['login_error'] = 'Vyplňte email i heslo!';
            header('Location: /login');
            exit();
        }
        
        // Zapišeme, že hodnoty nejsou prázdné
        $logEntry = "SUCCESS: Email and password received\n";
        $logEntry .= "  Email: '" . $email . "' (length: " . strlen($email) . ")\n";
        $logEntry .= "  Password: SET (length: " . strlen($password) . ")\n";
        @file_put_contents($loginLogFile, $logEntry, FILE_APPEND);

        error_log("Looking up user with email: " . $email);
        $user = $this->model->getByEmail($email);

        if (!$user) {
            @LogHelper::login("Login failed - User not found - Email: " . $email . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $_SESSION['login_error'] = 'Uživatel s tímto e-mailem neexistuje. Zkontrolujte prosím zadanou e-mailovou adresu.';
            header('Location: /login');
            exit();
        }

        error_log("User found - ID: " . $user['id'] . ", Role: " . $user['role']);
        error_log("Verifying password...");

        if (!password_verify($password, $user['heslo'])) {
            @LogHelper::login("Login failed - Wrong password - Email: " . $email . ", User ID: " . $user['id'] . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $_SESSION['login_error'] = 'Nesprávné heslo. Zkuste to prosím znovu nebo použijte odkaz pro obnovu hesla.';
            header('Location: /login');
            exit();  
        }

        error_log("Password verified successfully");

        // Kontrola role - pouze uživatelé s rolí > 0 mají přístup do administrace
        if ($user['role'] <= 0) {
            @LogHelper::login("Login failed - Insufficient permissions - Email: " . $email . ", User ID: " . $user['id'] . ", Role: " . $user['role'] . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $_SESSION['login_error'] = 'Váš účet čeká na schválení administrátorem. Po schválení budete moci přistupovat do administrace.';
            header('Location: /login');
            exit();
        }

        error_log("Setting session variables...");
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['profil_foto'] = $user['profil_foto'];
        error_log("Session variables set - User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role']);
        
        // DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
        // $possibleLogPaths = [
        //     dirname(dirname(dirname(__DIR__))) . '/logs/debug_test.log',  // bicenc/logs/
        //     dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/debug_test.log',  // subdom/logs/
        // ];
        // $debugFile = file_exists($possibleLogPaths[1]) ? $possibleLogPaths[1] : $possibleLogPaths[0];
        // 
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Session ID: " . session_id() . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Session variables set - User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role'] . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - All session keys: " . implode(', ', array_keys($_SESSION ?? [])) . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - About to redirect to /admin\n", FILE_APPEND);

        // Logování úspěšného přihlášení
        @LogHelper::login("Login successful - Email: " . $email . ", User ID: " . $user['id'] . ", Role: " . $user['role'] . ", Name: " . ($user['jmeno'] ?? 'N/A') . " " . ($user['prijmeni'] ?? 'N/A') . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

        error_log("Redirecting to /admin...");
        
        // Zajistit, že session je uložena a cookie je nastavena
        $sessionId = session_id();
        $sessionName = session_name();
        $cookieParams = session_get_cookie_params();
        
        // DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
        // $possibleLogPaths = [
        //     dirname(dirname(dirname(__DIR__))) . '/logs/debug_test.log',  // bicenc/logs/
        //     dirname(dirname(dirname(dirname(__DIR__)))) . '/logs/debug_test.log',  // subdom/logs/
        // ];
        // $debugFile = file_exists($possibleLogPaths[1]) ? $possibleLogPaths[1] : $possibleLogPaths[0];
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Redirecting to /admin with session ID: " . $sessionId . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Session name: " . $sessionName . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Cookie params: " . print_r($cookieParams, true) . "\n", FILE_APPEND);
        
        // Vyčistit output buffer PŘED jakoukoli operací s cookie/header
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Uložit session a zavřít ji
        session_write_close();
        
        // Explicitně nastavit cookie PŘED redirectem
        // Musí být před jakýmkoli header() nebo výstupem
        $cookieExpire = $cookieParams['lifetime'] > 0 ? time() + $cookieParams['lifetime'] : 0;
        $cookiePath = $cookieParams['path'] ?: '/';
        // Pro prázdný domain použijeme null, aby se cookie nastavila pro aktuální hostname
        $cookieDomain = $cookieParams['domain'] ?: null;
        $cookieSecure = $cookieParams['secure'] ?: false;
        $cookieHttpOnly = $cookieParams['httponly'] ?: true;
        
        // Nastavit cookie pomocí setcookie() - musí být před header()
        // Použijeme starší syntaxi s explicitními parametry
        $cookieSet = setcookie(
            $sessionName,
            $sessionId,
            $cookieExpire,
            $cookiePath,
            $cookieDomain ?? '', // Pokud je null, použijeme prázdný string
            $cookieSecure,
            $cookieHttpOnly
        );
        
        // Pokud PHP verze >= 7.3, můžeme použít moderní syntaxi s SameSite
        if ($cookieSet && PHP_VERSION_ID >= 70300) {
            // Přepis cookie s SameSite atributem
            $cookieString = $sessionName . '=' . $sessionId;
            $cookieString .= '; Path=' . $cookiePath;
            if ($cookieDomain) {
                $cookieString .= '; Domain=' . $cookieDomain;
            }
            $cookieString .= '; SameSite=Lax';
            if ($cookieHttpOnly) {
                $cookieString .= '; HttpOnly';
            }
            if ($cookieSecure) {
                $cookieString .= '; Secure';
            }
            if ($cookieExpire > 0) {
                $cookieString .= '; Expires=' . gmdate('D, d M Y H:i:s \G\M\T', $cookieExpire);
            }
            header('Set-Cookie: ' . $cookieString, false);
        }
        
        // DEBUG LOGY ZAKOMENTOVÁNY - pro debug odkomentovat
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Cookie set result: " . ($cookieSet ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
        // @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - LOGIN - Cookie: " . $sessionName . " = " . $sessionId . " (path=" . $cookiePath . ", domain=" . ($cookieDomain ?? 'null->empty') . ")\n", FILE_APPEND);
        
        // Normální redirect na /admin - session cookie by měla fungovat normálně
        header('Location: /admin');
        exit();
    }

    // Odhlášení uživatele
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userId = $_SESSION['user_id'] ?? 'unknown';
        $userEmail = $_SESSION['email'] ?? 'unknown';
        $userRole = $_SESSION['role'] ?? 'unknown';
        @LogHelper::login("User logged out - User ID: " . $userId . ", Email: " . $userEmail . ", Role: " . $userRole . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        session_destroy();
        header('Location: /login');
        exit();
    }

    // Zobrazení registračního formuláře
    public function create()
    {
        $disableNavbar = true;
        $disableBootstrap = true;
        $css = ['login'];
        $adminTitle = "Registrace | Admin Panel - Cyklistickey magazín";
        
        $view = '../app/Views/Admin/users/create.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Zpracování registrace
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF kontrola
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['registration_error'] = 'Neplatný bezpečnostní token (CSRF). Zkuste to prosím znovu.';
            header('Location: /register');
            exit();
        }
        
        $data = [
            'email' => trim($_POST['email']),
            'heslo' => trim($_POST['heslo']),
            'confirm_heslo' => trim($_POST['confirm_heslo']),
            'role' => 0, // Výchozí role uživatele
            'public_visible' => 0, // Noví uživatelé nejsou veřejně viditelní
            'name' => trim($_POST['name']),
            'surname' => trim($_POST['surname']),
        ];

        // Validace povinných polí
        if (empty($data['email']) || empty($data['heslo']) || empty($data['confirm_heslo']) || empty($data['name']) || empty($data['surname'])) {
            $_SESSION['registration_error'] = 'Prosím vyplňte všechna povinná pole (email, jméno, příjmení, heslo a potvrzení hesla).';
            header('Location: /register');
            exit;
        }

        // Validace formátu emailu
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['registration_error'] = 'Zadejte prosím platnou e-mailovou adresu.';
            header('Location: /register');
            exit;
        }

        // Ověření shody hesel
        if ($data['heslo'] !== $data['confirm_heslo']) {
            $_SESSION['registration_error'] = 'Hesla se neshodují. Zkontrolujte, že jste zadali stejné heslo v obou polích.';
            header('Location: /register');
            exit;
        }

        // Kontrola minimální délky hesla
        if (strlen($data['heslo']) < 6) {
            $_SESSION['registration_error'] = 'Heslo musí obsahovat alespoň 6 znaků.';
            header('Location: /register');
            exit;
        }

        // Kontrola, zda e-mail již existuje
        if ($this->model->checkEmailExists($data['email'])) {
            $_SESSION['registration_error'] = 'Účet s tímto e-mailem již existuje. Zkuste se přihlásit nebo použijte jiný e-mail.';
            header('Location: /register');
            exit;
        }

        // Uložení uživatele do databáze
        if ($this->model->createUser($data)) {
            @LogHelper::login("User registered - Email: " . $data['email'] . ", Name: " . $data['name'] . " " . $data['surname']);
            $_SESSION['login_success'] = 'Registrace byla úspěšná! Nyní se můžete přihlásit. Váš účet čeká na schválení administrátorem.';
            header('Location: /login');
            exit;
        } else {
            $_SESSION['registration_error'] = 'Chyba při registraci. Zkuste to prosím znovu. Pokud problém přetrvá, kontaktujte administrátora.';
            header('Location: /register');
            exit;
        }
    }

    // Zobrazení formuláře pro reset hesla
    public function reset()
    {
        $disableNavbar = true;
        $disableBootstrap = true;
        $css = ['login'];
        $adminTitle = "Reset hesla | Admin Panel - Cyklistickey magazín";
        
        $view = '../app/Views/Admin/users/reset_password.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Uloží token a zapíše do logu
    public function resetPassword()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF kontrola
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['reset_error'] = 'Neplatný bezpečnostní token (CSRF). Zkuste to prosím znovu.';
            header('Location: /reset-password');
            exit();
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $_SESSION['reset_error'] = 'Prosím vyplňte e-mailovou adresu.';
            header('Location: /reset-password');
            exit();
        }

        // Validace formátu emailu
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['reset_error'] = 'Zadejte prosím platnou e-mailovou adresu.';
            header('Location: /reset-password');
            exit();
        }
        
        error_log("DEBUG: Začínám reset hesla pro email: " . $email);
        
        $user = $this->model->getByEmail($email);

        if (!$user) {
            error_log("ERROR: Uživatel s emailem " . $email . " neexistuje");
            $_SESSION['reset_error'] = 'Účet s tímto e-mailem neexistuje. Zkontrolujte prosím zadanou e-mailovou adresu.';
            header('Location: /reset-password');
            exit();
        }

        error_log("DEBUG: Uživatel nalezen, ID: " . $user['id']);

        // Vygenerujeme token a čas expirace
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        error_log("DEBUG: Vygenerován token: " . $token . ", expirace: " . $expiresAt);

        // Nejprve zkontrolujeme, zda token byl úspěšně uložen do databáze
        $tokenSaved = $this->model->storeResetToken($user['id'], $email, $token, $expiresAt);
        
        if (!$tokenSaved) {
            error_log("ERROR: Chyba při ukládání tokenu do databáze");
            $_SESSION['reset_error'] = 'Chyba při generování resetovacího odkazu. Zkuste to prosím znovu.';
            header('Location: /reset-password');
            exit();
        }
        
        // Pro účely ladění vypíšeme informace do error_log
        error_log("DEBUG: Token úspěšně uložen do DB: " . $token . " pro uživatele ID: " . $user['id'] . ", email: " . $email);
        
        // Nyní, když víme, že token byl uložen, vytvoříme odkaz
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . urlencode($token);
        
        @LogHelper::login("Password reset requested - Email: " . $email . ", User ID: " . $user['id']);
        error_log("DEBUG: Vytvořen reset link: " . $resetLink);

        // Místo echo HTML stránky, uložíme odkaz do session a přesměrujeme
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['reset_link'] = $resetLink;
        
        // Přesměrování na novou stránku, která zobrazí resetovací odkaz
        header('Location: /reset-password/link');
        exit;
    }

    // Zobrazení stránky s odkazem pro reset hesla
    public function showResetLink()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['reset_link'])) {
            header('Location: /reset-password');
            exit;
        }
        
        $resetLink = $_SESSION['reset_link'];
        unset($_SESSION['reset_link']); // Smažeme link ze session po zobrazení
        
        $disableNavbar = true;
        $disableBootstrap = true;
        $css = ['login'];
        $adminTitle = "Odkaz pro reset hesla | Admin Panel - Cyklistickey magazín";
        
        $view = '../app/Views/Admin/users/reset_link.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Zobrazení formuláře pro nastavení nového hesla
    public function confirmResetPassword()
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            header('Location: /reset-password');
            exit;
        }
        
        // Ověření platnosti tokenu
        $resetInfo = $this->model->getValidResetToken($token);
        
        if (!$resetInfo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['reset_error'] = 'Neplatný nebo expirovaný token pro reset hesla. Požádejte prosím o nový odkaz.';
            header('Location: /reset-password');
            exit;
        }
        
        $disableNavbar = true;
        $disableBootstrap = true;
        $css = ['login'];
        $adminTitle = "Nastavení nového hesla | Admin Panel - Cyklistickey magazín";
        
        $view = '../app/Views/Admin/users/new_password.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function saveNewPassword()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF kontrola
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $token = trim($_POST['token'] ?? '');
            $_SESSION['reset_error'] = 'Neplatný bezpečnostní token (CSRF). Zkuste to prosím znovu.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit();
        }

        $token = trim($_POST['token'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Kontrola přítomnosti povinných údajů
        if (empty($token)) {
            error_log("ERROR: Chybí token v požadavku pro reset hesla.");
            $_SESSION['reset_error'] = 'Chybí token pro změnu hesla. Požádejte prosím o nový odkaz.';
            header('Location: /reset-password');
            exit;
        }

        if (empty($newPassword) || empty($confirmPassword)) {
            error_log("ERROR: Chybí heslo v požadavku pro reset hesla. Token: " . $token);
            $_SESSION['reset_error'] = 'Prosím vyplňte obě pole s heslem.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        // Kontrola minimální délky hesla
        if (strlen($newPassword) < 6) {
            $_SESSION['reset_error'] = 'Heslo musí obsahovat alespoň 6 znaků.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            error_log("ERROR: Hesla se neshodují při resetu hesla. Token: " . $token);
            $_SESSION['reset_error'] = 'Hesla se neshodují. Zkontrolujte, že jste zadali stejné heslo v obou polích.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }

        // Získání dat o resetu hesla
        $resetData = $this->model->getValidResetToken($token);
        
        if (!$resetData) {
            @LogHelper::login("Password reset failed - Invalid or expired token");
            error_log("ERROR: Token pro reset hesla nebyl nalezen v databázi nebo expiroval: " . $token);
            $_SESSION['reset_error'] = 'Token je neplatný nebo expirovaný. Požádejte prosím o nový odkaz.';
            header('Location: /reset-password');
            exit;
        }

        // Kontrola, zda e-mail existuje v DB
        $user = $this->model->getByEmail($resetData['email']);
        if (!$user) {
            error_log("ERROR: Uživatel s emailem " . $resetData['email'] . " nebyl nalezen.");
            $_SESSION['reset_error'] = 'Účet s tímto e-mailem neexistuje.';
            header('Location: /reset-password');
            exit;
        }

        // Aktualizace hesla podle user_id
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = $this->model->updatePassword($user['id'], $hashedPassword);
        
        if ($updated) {
            // Smazání použitého tokenu
            $this->model->deleteResetToken($token);
            @LogHelper::login("Password reset completed - User ID: " . $user['id'] . ", Email: " . $resetData['email']);
            error_log("SUCCESS: Heslo bylo úspěšně změněno pro uživatele ID: " . $user['id']);
            $_SESSION['login_success'] = 'Heslo bylo úspěšně změněno! Nyní se můžete přihlásit s novým heslem.';
            header('Location: /login');
            exit;
        } else {
            @LogHelper::login("Password reset failed - Update error for User ID: " . $user['id']);
            error_log("ERROR: Chyba při změně hesla pro uživatele ID: " . $user['id']);
            $_SESSION['reset_error'] = 'Chyba při změně hesla. Zkuste to prosím znovu.';
            header('Location: /reset-password?token=' . urlencode($token));
            exit;
        }
    }
}
