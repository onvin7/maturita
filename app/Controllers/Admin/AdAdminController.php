<?php

namespace App\Controllers\Admin;

use PDO;
use App\Models\Ad;
use App\Helpers\LogHelper;
use DateTime;

class AdAdminController
{
    private $db;
    private $adModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->adModel = new Ad($db);
    }

    // Zobrazení seznamu všech reklam
    public function index()
    {
        $ads = $this->adModel->getAllAds();

        $adminTitle = "Správa reklam | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/ads/index.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Zobrazení stránky pro přidání reklamy
    public function create()
    {
        // Aktuální datum a čas pro minimální hodnotu data v HTML
        $currentDateTime = new DateTime();
        $minDateTime = $currentDateTime->format('Y-m-d\TH:i');

        $adminTitle = "Vytvořit reklamu | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/ads/create.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Uložení nové reklamy
    public function store()
    {
        // Kontrola, zda jsou všechny potřebné údaje přítomny
        if (
            !isset($_POST['nazev']) || !isset($_POST['odkaz']) ||
            !isset($_POST['start_date']) || !isset($_POST['start_time']) ||
            !isset($_POST['end_date']) || !isset($_POST['end_time'])
        ) {
            $_SESSION['errors'] = ["Všechna povinná pole musí být vyplněna."];
            header("Location: /admin/ads/create");
            exit();
        }

        // Kontrola nahrání obrázku
        if (!isset($_FILES['obrazek']) || $_FILES['obrazek']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] = ["Musíte nahrát obrázek reklamy."];
            header("Location: /admin/ads/create");
            exit();
        }

        $nazev = trim($_POST['nazev']);
        $odkaz = trim($_POST['odkaz']);

        // Kombinujeme datum a čas do formátu pro databázi
        $zacatek_datum = $_POST['start_date'];
        $zacatek_cas = $_POST['start_time'];
        $zacatek = $zacatek_datum . ' ' . $zacatek_cas . ':00';

        $konec_datum = $_POST['end_date'];
        $konec_cas = $_POST['end_time'];
        $konec = $konec_datum . ' ' . $konec_cas . ':00';

        $aktivni = isset($_POST['aktivni']) ? 1 : 0;
        $vychozi = isset($_POST['vychozi']) ? 1 : 0;
        $frekvence = isset($_POST['frekvence']) ? (int)$_POST['frekvence'] : 1;

        $user_id = $_SESSION['user_id'];

        // Validace dat
        $errors = [];

        if (empty($nazev)) {
            $errors[] = "Název reklamy je povinný.";
        }

        if (empty($odkaz) || !filter_var($odkaz, FILTER_VALIDATE_URL)) {
            $errors[] = "Odkaz musí být platná URL adresa.";
        }

        $now = new DateTime();
        $startDate = new DateTime($zacatek);
        $endDate = new DateTime($konec);

        // Ověříme, že konec je po začátku
        if ($endDate <= $startDate) {
            $errors[] = "Konec reklamy musí být po začátku.";
        }

        if ($frekvence < 1) {
            $errors[] = "Frekvence musí být alespoň 1.";
        }

        // Pokud jsou chyby, vrátíme uživatele zpět
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /admin/ads/create");
            exit();
        }

        // Nahrání obrázku
        $obrazek = $this->uploadImage($_FILES['obrazek']);
        if (!$obrazek) {
            $_SESSION['errors'] = ["Chyba při nahrávání obrázku."];
            header("Location: /admin/ads/create");
            exit();
        }

        // Pokud je nastavena jako výchozí, zrušíme ostatní výchozí
        if ($vychozi) {
            $this->db->exec("UPDATE reklamy SET vychozi = 0");
        }

        // Uložíme reklamu
        $adData = [
            'nazev' => $nazev,
            'obrazek' => $obrazek,
            'odkaz' => $odkaz,
            'zacatek' => $zacatek,
            'konec' => $konec,
            'aktivni' => $aktivni,
            'vychozi' => $vychozi,
            'frekvence' => $frekvence,
            'user_id' => $user_id
        ];

        $adId = $this->adModel->createAd($adData);

        @LogHelper::admin('Ad created', 'Ad ID: ' . $adId . ', Name: ' . $nazev);
        $_SESSION['success'] = "Reklama byla úspěšně vytvořena.";
        header("Location: /admin/ads");
        exit();
    }

    // Zobrazení stránky pro editaci reklamy
    public function edit($id)
    {
        $ad = $this->adModel->getAdById($id);

        if (!$ad) {
            $_SESSION['errors'] = ["Reklama nebyla nalezena."];
            header("Location: /admin/ads");
            exit();
        }

        // Aktuální datum a čas pro minimální hodnotu data v HTML
        $currentDateTime = new DateTime();
        $minDateTime = $currentDateTime->format('Y-m-d\TH:i');

        $adminTitle = "Upravit reklamu | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/ads/edit.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Aktualizace reklamy
    public function update($id)
    {
        $ad = $this->adModel->getAdById($id);

        if (!$ad) {
            $_SESSION['errors'] = ["Reklama nebyla nalezena."];
            header("Location: /admin/ads");
            exit();
        }

        // Kontrola, zda jsou všechny potřebné údaje přítomny
        if (
            !isset($_POST['nazev']) || !isset($_POST['odkaz']) ||
            !isset($_POST['start_date']) || !isset($_POST['start_time']) ||
            !isset($_POST['end_date']) || !isset($_POST['end_time'])
        ) {
            $_SESSION['errors'] = ["Všechna povinná pole musí být vyplněna."];
            header("Location: /admin/ads/edit/" . $id);
            exit();
        }

        $nazev = trim($_POST['nazev']);
        $odkaz = trim($_POST['odkaz']);

        // Kombinujeme datum a čas do formátu pro databázi
        $zacatek_datum = $_POST['start_date'];
        $zacatek_cas = $_POST['start_time'];
        $zacatek = $zacatek_datum . ' ' . $zacatek_cas . ':00';

        $konec_datum = $_POST['end_date'];
        $konec_cas = $_POST['end_time'];
        $konec = $konec_datum . ' ' . $konec_cas . ':00';

        $aktivni = isset($_POST['aktivni']) ? 1 : 0;
        $vychozi = isset($_POST['vychozi']) ? 1 : 0;
        $frekvence = isset($_POST['frekvence']) ? (int)$_POST['frekvence'] : 1;

        // Validace dat
        $errors = [];

        if (empty($nazev)) {
            $errors[] = "Název reklamy je povinný.";
        }

        if (empty($odkaz) || !filter_var($odkaz, FILTER_VALIDATE_URL)) {
            $errors[] = "Odkaz musí být platná URL adresa.";
        }

        $startDate = new DateTime($zacatek);
        $endDate = new DateTime($konec);

        // Ověříme, že konec je po začátku
        if ($endDate <= $startDate) {
            $errors[] = "Konec reklamy musí být po začátku.";
        }

        if ($frekvence < 1) {
            $errors[] = "Frekvence musí být alespoň 1.";
        }

        // Pokud jsou chyby, vrátíme uživatele zpět
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /admin/ads/edit/" . $id);
            exit();
        }

        // Nahrání nového obrázku, pokud byl nahrán
        $obrazek = $ad['obrazek']; // Použijeme stávající
        if (isset($_FILES['obrazek']) && $_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
            // Smazat starý obrázek
            $oldImagePath = __DIR__ . '/../../../web/uploads/ads/' . $ad['obrazek'];
            if (file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }

            $newImage = $this->uploadImage($_FILES['obrazek']);
            if ($newImage) {
                $obrazek = $newImage;
            }
        }

        // Pokud je nastavena jako výchozí, zrušíme ostatní výchozí
        if ($vychozi && !$ad['vychozi']) {
            $this->db->exec("UPDATE reklamy SET vychozi = 0");
        }

        // Aktualizujeme reklamu
        $adData = [
            'nazev' => $nazev,
            'obrazek' => $obrazek,
            'odkaz' => $odkaz,
            'zacatek' => $zacatek,
            'konec' => $konec,
            'aktivni' => $aktivni,
            'vychozi' => $vychozi,
            'frekvence' => $frekvence
        ];

        $this->adModel->updateAd($id, $adData);

        @LogHelper::admin('Ad updated', 'Ad ID: ' . $id . ', Name: ' . $nazev);
        $_SESSION['success'] = "Reklama byla úspěšně aktualizována.";
        header("Location: /admin/ads");
        exit();
    }

    // Smazání reklamy
    public function delete($id)
    {
        $ad = $this->adModel->getAdById($id);

        if (!$ad) {
            $_SESSION['errors'] = ["Reklama nebyla nalezena."];
            header("Location: /admin/ads");
            exit();
        }

        // Smazání obrázku
        $imagePath = __DIR__ . '/../../../web/uploads/ads/' . $ad['obrazek'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }

        // Smazání reklamy
        $this->adModel->deleteAd($id);

        @LogHelper::admin('Ad deleted', 'Ad ID: ' . $id);
        $_SESSION['success'] = "Reklama byla úspěšně smazána.";
        header("Location: /admin/ads");
        exit();
    }

    // Přepnutí aktivace reklamy
    public function toggleActive($id)
    {
        $ad = $this->adModel->getAdById($id);

        if (!$ad) {
            $_SESSION['errors'] = ["Reklama nebyla nalezena."];
            header("Location: /admin/ads");
            exit();
        }

        $this->adModel->toggleActive($id);

        @LogHelper::admin('Ad toggled', 'Ad ID: ' . $id . ', Active: ' . (!$ad['aktivni'] ? '1' : '0'));
        $_SESSION['success'] = "Stav reklamy byl změněn.";
        header("Location: /admin/ads");
        exit();
    }

    // Nastavení reklamy jako výchozí
    public function setDefault($id)
    {
        $ad = $this->adModel->getAdById($id);

        if (!$ad) {
            $_SESSION['errors'] = ["Reklama nebyla nalezena."];
            header("Location: /admin/ads");
            exit();
        }

        $this->adModel->setDefault($id);

        @LogHelper::admin('Ad set as default', 'Ad ID: ' . $id);
        $_SESSION['success'] = "Reklama byla nastavena jako výchozí.";
        header("Location: /admin/ads");
        exit();
    }

    /**
     * Nahrání obrázku reklamy
     * 
     * @param array $file
     * @return string|false Název souboru nebo false při chybě
     */
    private function uploadImage($file)
    {
        $targetDir = __DIR__ . '/../../../web/uploads/ads/';

        // Vytvoření adresáře, pokud neexistuje
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Kontrola typu souboru - povolujeme pouze obrázky
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $file['type'];

        if (!in_array($fileType, $allowedTypes)) {
            return false;
        }

        // Generování unikátního názvu souboru
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('ad_', true) . '.' . $extension;
        $targetPath = $targetDir . $uniqueName;

        // Přesun souboru
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $uniqueName;
        }

        return false;
    }
}


