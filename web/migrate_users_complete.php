<?php
/**
 * Komplexní migrační skript pro uživatele
 * - Přenese uživatele z DB do DB
 * - Přenese resety hesel
 * - Stáhne a aktualizuje profilové fotky
 * - Zmenší profilové fotky
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
ini_set('memory_limit', '1024M');

// Pro webový výstup - vypnout buffering pro průběžný výstup
if (php_sapi_name() !== 'cli') {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: text/html; charset=utf-8');
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', 1);
    }
    @ini_set('zlib.output_compression', 0);
}

function zprava($text) {
    echo $text . (php_sapi_name() === 'cli' ? "\n" : "<br>\n");
    if (php_sapi_name() !== 'cli') {
        flush();
        if (ob_get_level() > 0) {
            ob_flush();
        }
    }
}

// Konfigurace databází
$old_db_config = [
    'host' => 'md396.wedos.net',
    'username' => 'w340619_clanky',
    'password' => 'bqsUuxcr',
    'database' => 'd340619_clanky'
];

$new_db_config = [
    'host' => 'md413.wedos.net',
    'username' => 'w340619_blog',
    'password' => 'kaYak714?',
    'database' => 'd340619_blog'
];

function connectDB($config, $label) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        zprava("Připojování k databázi $label...");
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        $pdo->exec("SET NAMES 'utf8mb4'");
        return $pdo;
    } catch (PDOException $e) {
        zprava("❌ Chyba připojení k databázi $label: " . $e->getMessage());
        die();
    }
}

// Funkce pro resize (zkopírováno z migrate_images.php)
function resizeUserPhoto($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    list($originalWidth, $originalHeight, $imageType) = @getimagesize($filePath);
    if (!$originalWidth || !$originalHeight) {
        return false;
    }
    
    $maxSize = 400;
    
    // Načtení obrázku
    $source = null;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $source = @imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $source = @imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_GIF:
            $source = @imagecreatefromgif($filePath);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // Ořez na čtverec podle menší strany
    $size = min($originalWidth, $originalHeight);
    $srcX = ($originalWidth - $size) / 2;
    $srcY = ($originalHeight - $size) / 2;
    
    $croppedImage = imagecreatetruecolor($size, $size);
    
    // Zachování průhlednosti pro PNG
    if ($imageType === IMAGETYPE_PNG) {
        imagealphablending($croppedImage, false);
        imagesavealpha($croppedImage, true);
    }
    
    imagecopyresampled($croppedImage, $source, 0, 0, $srcX, $srcY, $size, $size, $size, $size);
    
    // Změna velikosti na 400x400
    $resizedImage = imagecreatetruecolor($maxSize, $maxSize);
    
    if ($imageType === IMAGETYPE_PNG) {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
    }
    
    imagecopyresampled($resizedImage, $croppedImage, 0, 0, 0, 0, $maxSize, $maxSize, $size, $size);
    
    // Uložení
    $result = false;
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($resizedImage, $filePath, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($resizedImage, $filePath, 1);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($resizedImage, $filePath);
            break;
    }
    
    imagedestroy($resizedImage);
    imagedestroy($croppedImage);
    imagedestroy($source);
    
    return $result;
}

// ============================================================================
// HLAVNÍ LOGIKA
// ============================================================================

zprava("<h1>🚀 KOMPLEXNÍ MIGRACE UŽIVATELŮ</h1>");
zprava("<p>Tento skript provede vše potřebné pro uživatele v jednom kroku.</p>");

$pdo_old = connectDB($old_db_config, 'STARÁ DB');
$pdo_new = connectDB($new_db_config, 'NOVÁ DB');
$pdo_new->exec("SET FOREIGN_KEY_CHECKS=0");

// 1. MIGRACE UŽIVATELŮ (DB)
zprava("<h2>1. Migrace tabulky users</h2>");
$stmt_old = $pdo_old->query("SELECT id, email, heslo, admin, name, surname, profil_foto, popis, datum FROM users ORDER BY id");
$users = $stmt_old->fetchAll(PDO::FETCH_ASSOC);
zprava("Načteno " . count($users) . " uživatelů.");

$processed_users = 0;
$stmt_insert = $pdo_new->prepare("
    INSERT INTO users (id, email, heslo, role, name, surname, profil_foto, popis, datum) 
    VALUES (:id, :email, :heslo, :role, :name, :surname, :profil_foto, :popis, :datum)
    ON DUPLICATE KEY UPDATE 
        email = VALUES(email), heslo = VALUES(heslo), role = VALUES(role),
        name = VALUES(name), surname = VALUES(surname),
        profil_foto = VALUES(profil_foto), popis = VALUES(popis), datum = VALUES(datum)
");

foreach ($users as $user) {
    // Prozatím prázdná fotka, doplníme v kroku 3
    $stmt_insert->execute([
        ':id' => $user['id'],
        ':email' => $user['email'],
        ':heslo' => $user['heslo'],
        ':role' => $user['admin'],
        ':name' => $user['name'],
        ':surname' => $user['surname'],
        ':profil_foto' => '', 
        ':popis' => $user['popis'],
        ':datum' => $user['datum']
    ]);
    $processed_users++;
}
zprava("✓ Migrováno $processed_users záznamů v DB.");

// 2. MIGRACE RESETŮ HESEL
zprava("<h2>2. Migrace resetů hesel</h2>");
$stmt_old = $pdo_old->query("SELECT id, user_id, email, token, expires_at FROM password_resets WHERE expires_at >= NOW()");
$resets = $stmt_old->fetchAll(PDO::FETCH_ASSOC);

$stmt_new = $pdo_new->prepare("
    INSERT INTO password_resets (id, user_id, email, token, expires_at) 
    VALUES (:id, :user_id, :email, :token, :expires_at)
    ON DUPLICATE KEY UPDATE 
        user_id = VALUES(user_id), email = VALUES(email), token = VALUES(token), expires_at = VALUES(expires_at)
");

$processed_resets = 0;
foreach ($resets as $reset) {
    // Kontrola existence usera
    $stmt_check = $pdo_new->prepare("SELECT id FROM users WHERE id = ?");
    $stmt_check->execute([$reset['user_id']]);
    if ($stmt_check->fetch()) {
        $stmt_new->execute([
            ':id' => $reset['id'],
            ':user_id' => $reset['user_id'],
            ':email' => $reset['email'],
            ':token' => $reset['token'],
            ':expires_at' => $reset['expires_at']
        ]);
        $processed_resets++;
    }
}
zprava("✓ Migrováno $processed_resets resetů hesel.");

// 3. STAŽENÍ A ÚPRAVA FOTEK
zprava("<h2>3. Stažení a optimalizace profilových fotek</h2>");

$new_photo_path = $_SERVER['DOCUMENT_ROOT'] . '/web/uploads/users/thumbnails/';
if (!is_dir($new_photo_path)) {
    mkdir($new_photo_path, 0777, true);
    zprava("Vytvořena složka: $new_photo_path");
}

$old_photo_paths = [
    '/data/web/virtuals/340619/virtual/www/subdom/magazin/assets/img/upload/profil_foto/',
    'https://www.magazin.cyklistickey.cz/assets/img/upload/profil_foto/'
];

// Načteme uživatele, kteří mají fotku
$stmt_users_with_photo = $pdo_new->query("SELECT id FROM users"); // Projdeme všechny v nové DB a podíváme se do staré DB co měli za fotku
// Lepší přístup: Použijeme pole $users načtené v kroku 1, protože tam máme info o staré fotce

$downloaded = 0;
$resized = 0;
$updated_db = 0;

$stmt_update_photo = $pdo_new->prepare("UPDATE users SET profil_foto = :foto WHERE id = :id");

foreach ($users as $user) {
    $profil_foto = $user['profil_foto'];
    if (empty($profil_foto)) continue;
    
    // Clean filename
    if (strpos($profil_foto, '/') !== false || strpos($profil_foto, '\\') !== false) {
        $profil_foto = basename($profil_foto);
    }
    
    $target_file = $new_photo_path . $profil_foto;
    $got_file = false;
    
    // 3a. Stažení souboru
    if (file_exists($target_file) && filesize($target_file) > 0) {
        $got_file = true; // Už máme
    } else {
        foreach ($old_photo_paths as $old_path) {
            $source = $old_path . $profil_foto;
            if (strpos($source, 'http') === 0) {
                $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
                $content = @file_get_contents($source, false, $ctx);
                if ($content && strlen($content) > 0) {
                    file_put_contents($target_file, $content);
                    $got_file = true;
                    $downloaded++;
                    break;
                }
            } else {
                if (@file_exists($source)) {
                    @copy($source, $target_file);
                    $got_file = true;
                    $downloaded++;
                    break;
                }
            }
        }
    }
    
    if ($got_file) {
        // 3b. Resize
        if (resizeUserPhoto($target_file)) {
            $resized++;
        }
        
        // 3c. Update DB
        $stmt_update_photo->execute([':foto' => $profil_foto, ':id' => $user['id']]);
        $updated_db++;
        
        if ($updated_db % 10 == 0) zprava("Zpracováno $updated_db fotek...");
    } else {
        zprava("⚠️ Fotka nenalezena: $profil_foto (User ID: {$user['id']})");
    }
}

zprava("✓ Staženo: $downloaded");
zprava("✓ Zmenšeno: $resized");
zprava("✓ Aktualizováno v DB: $updated_db");

$pdo_new->exec("SET FOREIGN_KEY_CHECKS=1");

zprava("<h1>✅ HOTOVO! Všichni uživatelé jsou kompletně migrováni.</h1>");
?>
