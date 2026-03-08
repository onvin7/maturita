<?php
/**
 * MASTER MIGRATION SCRIPT (Data Only)
 * 
 * Tento skript provede kompletní migraci databáze z původního webu na nový.
 * 
 * CO DĚLÁ:
 * - Přenáší kompletní strukturu dat (kategorie, uživatele, články, statistiky...)
 * - Stahuje textový obsah článků z HTML souborů
 * - Opravuje cesty k obrázkům v textu článků (aby fungovaly na novém webu)
 * - Propojuje audio soubory a obrázky v DB (ale fyzické soubory nepřenáší - to uděláš ručně)
 * 
 * CO NEDĚLÁ:
 * - Nestahuje obrázky ani audio soubory (předpokládá se ruční upload přes FTP)
 */

// Nastavení pro běh bez limitů
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);           // Neomezený čas běhu
ini_set('memory_limit', '2048M'); // Hodně paměti pro jistotu

// Pro webový výstup - vypnout buffering
if (php_sapi_name() !== 'cli') {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    if (function_exists('apache_setenv')) @apache_setenv('no-gzip', 1);
    @ini_set('zlib.output_compression', 0);
}

function zprava($text, $type = 'info') {
    $color = '#333';
    if ($type === 'success') $color = 'green';
    if ($type === 'error') $color = 'red';
    if ($type === 'warning') $color = 'orange';
    
    echo "<div style='color: {$color}; margin-bottom: 2px; font-family: monospace;'>" . $text . "</div>";
    
    if (php_sapi_name() !== 'cli') {
        flush();
        if (ob_get_level() > 0) ob_flush();
    }
}

// === KONFIGURACE ===

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

// Cesty pro stahování HTML obsahu
$old_html_paths = [
    '/data/web/virtuals/340619/virtual/www/subdom/magazin/assets/html/clanek_',
    'https://www.magazin.cyklistickey.cz/assets/html/clanek_'
];

// === POMOCNÉ FUNKCE ===

function connectDB($config, $label) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ]);
        $pdo->exec("SET NAMES 'utf8mb4'");
        return $pdo;
    } catch (PDOException $e) {
        zprava("❌ Chyba připojení k $label: " . $e->getMessage(), 'error');
        die();
    }
}

// === START MIGRACE ===

zprava("<h1>🚀 MASTER MIGRATION START</h1>");
zprava("Připojuji se k databázím...");

$pdo_old = connectDB($old_db_config, 'STARÁ DB');
$pdo_new = connectDB($new_db_config, 'NOVÁ DB');

// Vypnutí kontroly cizích klíčů
$pdo_new->exec("SET FOREIGN_KEY_CHECKS=0");


// --- 1. KATEGORIE ---
zprava("<h2>1. Kategorie</h2>");
$kategorie = $pdo_old->query("SELECT id, nazev_kategorie, url FROM kategorie ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("INSERT INTO kategorie (id, nazev_kategorie, url) VALUES (:id, :nazev, :url) ON DUPLICATE KEY UPDATE nazev_kategorie=VALUES(nazev_kategorie), url=VALUES(url)");

foreach ($kategorie as $row) {
    $stmt->execute([':id' => $row['id'], ':nazev' => $row['nazev_kategorie'], ':url' => $row['url']]);
}
zprava("✓ Kategorie migrovány (" . count($kategorie) . ")", 'success');


// --- 2. UŽIVATELÉ ---
zprava("<h2>2. Uživatelé</h2>");
$users = $pdo_old->query("SELECT * FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("
    INSERT INTO users (id, email, heslo, role, name, surname, profil_foto, popis, datum) 
    VALUES (:id, :email, :heslo, :role, :name, :surname, :profil_foto, :popis, :datum)
    ON DUPLICATE KEY UPDATE email=VALUES(email), heslo=VALUES(heslo), role=VALUES(role), name=VALUES(name), surname=VALUES(surname), profil_foto=VALUES(profil_foto), popis=VALUES(popis), datum=VALUES(datum)
");

foreach ($users as $user) {
    // Oprava názvu fotky (jen basename)
    $foto = $user['profil_foto'] ? basename($user['profil_foto']) : '';
    
    $stmt->execute([
        ':id' => $user['id'],
        ':email' => $user['email'],
        ':heslo' => $user['heslo'],
        ':role' => $user['admin'], // admin -> role
        ':name' => $user['name'],
        ':surname' => $user['surname'],
        ':profil_foto' => $foto,
        ':popis' => $user['popis'],
        ':datum' => $user['datum']
    ]);
}
zprava("✓ Uživatelé migrováni (" . count($users) . ")", 'success');


// --- 3. ČLÁNKY (Nejnáročnější část) ---
zprava("<h2>3. Články (včetně stahování obsahu)</h2>");
$clanky = $pdo_old->query("SELECT id, nazev, datum, viditelnost, nahled_foto, user_id, url FROM clanky ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
zprava("Načteno " . count($clanky) . " článků k migraci.");

$stmt_check_user = $pdo_new->prepare("SELECT id FROM users WHERE id = ?");
$stmt_insert = $pdo_new->prepare("
    INSERT INTO clanky (id, nazev, datum, viditelnost, nahled_foto, obsah, user_id, url) 
    VALUES (:id, :nazev, :datum, :viditelnost, :nahled_foto, :obsah, :user_id, :url)
    ON DUPLICATE KEY UPDATE nazev=VALUES(nazev), datum=VALUES(datum), viditelnost=VALUES(viditelnost), nahled_foto=VALUES(nahled_foto), obsah=VALUES(obsah), user_id=VALUES(user_id), url=VALUES(url)
");

$counter = 0;
foreach ($clanky as $clanek) {
    // 1. Kontrola User ID
    $user_id = $clanek['user_id'];
    $stmt_check_user->execute([$user_id]);
    if (!$stmt_check_user->fetch()) $user_id = 0; // Fallback na 0
    
    // 2. Náhledové foto (jen basename)
    $nahled_foto = $clanek['nahled_foto'] ? basename($clanek['nahled_foto']) : null;

    // 3. Stahování HTML obsahu
    $obsah = '';
    foreach ($old_html_paths as $base) {
        foreach ['.html', '.php'] as $ext) {
            $url = $base . $clanek['id'] . $ext;
            if (strpos($url, 'http') === 0) {
                // Stahování přes HTTP
                $ctx = stream_context_create(['http' => ['timeout' => 3, 'ignore_errors' => true]]);
                $content = @file_get_contents($url, false, $ctx);
            } else {
                // Lokální soubor
                $content = @file_get_contents($url);
            }
            
            if ($content && strlen($content) > 0) {
                $obsah = $content;
                break 2;
            }
        }
    }

    // 4. SEARCH & REPLACE v obsahu (Oprava cest k obrázkům)
    // Stará cesta: /assets/img/upload/clanek_obsah/neco.jpg
    // Nová cesta: /web/uploads/articles/neco.jpg
    // Hledáme různé varianty starých cest
    $obsah = str_replace(
        ['/assets/img/upload/clanek_obsah/', 'assets/img/upload/clanek_obsah/'], 
        '/web/uploads/articles/', 
        $obsah
    );

    // Uložení do DB
    $stmt_insert->execute([
        ':id' => $clanek['id'],
        ':nazev' => $clanek['nazev'],
        ':datum' => $clanek['datum'],
        ':viditelnost' => $clanek['viditelnost'],
        ':nahled_foto' => $nahled_foto,
        ':obsah' => $obsah,
        ':user_id' => $user_id,
        ':url' => $clanek['url']
    ]);

    $counter++;
    if ($counter % 50 == 0) zprava("Zpracováno $counter článků...");
}
zprava("✓ Články migrovány ($counter)", 'success');


// --- 4. VAZBY KATEGORIÍ ---
zprava("<h2>4. Vazby kategorií</h2>");
$vazby = $pdo_old->query("SELECT id_clanku, id_kategorie FROM kategorie_clanku")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("INSERT IGNORE INTO clanky_kategorie (id_clanku, id_kategorie) VALUES (:c, :k)");
$c = 0;
foreach ($vazby as $v) {
    // Kontrola existence (pro jistotu)
    $exists_c = $pdo_new->query("SELECT id FROM clanky WHERE id = {$v['id_clanku']}")->fetch();
    $exists_k = $pdo_new->query("SELECT id FROM kategorie WHERE id = {$v['id_kategorie']}")->fetch();
    
    if ($exists_c && $exists_k) {
        $stmt->execute([':c' => $v['id_clanku'], ':k' => $v['id_kategorie']]);
        $c++;
    }
}
zprava("✓ Vazby migrovány ($c)", 'success');


// --- 5. AUDIO (METADATA ONLY) ---
zprava("<h2>5. Audio soubory (Pouze DB záznamy)</h2>");
// Zde jen přeneseme názvy souborů. Uživatel nahraje soubory ručně.
$audio = $pdo_old->query("SELECT id_clanku, nazev_souboru FROM audio WHERE id_clanku > 0")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("UPDATE clanky SET audio = :file WHERE id = :id");
$c = 0;
foreach ($audio as $a) {
    // Jen basename pro jistotu
    $filename = basename($a['nazev_souboru']);
    $stmt->execute([':file' => $filename, ':id' => $a['id_clanku']]);
    $c++;
}
zprava("✓ Audio záznamy aktualizovány ($c). Fyzické soubory nahraj ručně do /web/uploads/audio/", 'warning');


// --- 6. PROPAGACE, VIEWS, RESET HEASL (Ostatní tabulky) ---
zprava("<h2>6. Ostatní data</h2>");

// Propagace
$prop = $pdo_old->query("SELECT * FROM propagace")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("INSERT INTO propagace (id, id_clanku, user_id, zacatek, konec) VALUES (:id, :id_c, 0, DATE_SUB(:d, INTERVAL 7 DAY), :d) ON DUPLICATE KEY UPDATE id_clanku=VALUES(id_clanku)");
foreach ($prop as $p) $stmt->execute([':id'=>$p['id'], ':id_c'=>$p['id_clanku'], ':d'=>$p['datum']]);
zprava("Propagace OK.");

// Views
$views = $pdo_old->query("SELECT * FROM views_clanku")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("INSERT INTO views_clanku (id, id_clanku, pocet, datum) VALUES (:id, :id_c, :p, :d) ON DUPLICATE KEY UPDATE pocet=VALUES(pocet)");
foreach ($views as $v) $stmt->execute([':id'=>$v['id'], ':id_c'=>$v['id_clanku'], ':p'=>$v['pocet'], ':d'=>$v['datum']]);
zprava("Views OK.");

// Password resets
$resets = $pdo_old->query("SELECT * FROM password_resets WHERE expires_at >= NOW()")->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo_new->prepare("INSERT IGNORE INTO password_resets (id, user_id, email, token, expires_at) VALUES (:id, :u, :e, :t, :ex)");
foreach ($resets as $r) {
    // Check user exists
    if ($pdo_new->query("SELECT id FROM users WHERE id={$r['user_id']}")->fetch()) {
        $stmt->execute([':id'=>$r['id'], ':u'=>$r['user_id'], ':e'=>$r['email'], ':t'=>$r['token'], ':ex'=>$r['expires_at']]);
    }
}
zprava("Resets OK.");


// Zapnutí cizích klíčů
$pdo_new->exec("SET FOREIGN_KEY_CHECKS=1");

zprava("<br><h1>✅ MIGRACE ÚSPĚŠNĚ DOKONČENA</h1>", 'success');
zprava("Nyní nahraj ručně složky s obrázky a audiem na FTP do příslušných složek ve /web/uploads/.");
?>
