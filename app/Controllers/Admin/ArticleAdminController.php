<?php

namespace App\Controllers\Admin;

use App\Models\Article;
use App\Models\Category;

use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatefromgif;

class ArticleAdminController
{
    private $model;
    private $articleModel;

    public function __construct($db)
    {
        $this->model = $db;
        $this->articleModel = new Article($db);
    }

    public function index()
    {
        $sortBy = $_GET['sort_by'] ?? 'datum'; // Výchozí řazení podle data
        $order = $_GET['order'] ?? 'DESC';    // Výchozí sestupné řazení
        $filter = $_GET['filter'] ?? '';      // Výchozí bez filtru

        $articles = $this->articleModel->getAllWithSortingAndFiltering($sortBy, $order, $filter);

        $view = '../../app/Views/Admin/articles/index.php';
        include '../../app/Views/Admin/layout/base.php';
    }


    // Formulář pro vytvoření článku
    public function create()
    {
        $categoryModel = new Category($this->model); // Použití modelu kategorie
        $categories = $categoryModel->getAll(); // Načtení kategorií
        $view = '../../app/Views/Admin/articles/create.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    // Ukládání nového článku
    public function store($postData)
    {
        if (empty($postData['nazev'])) {
            echo "Název článku je povinný.";
            return;
        }
        if (empty($postData['content'])) {
            echo "Obsah článku je povinný.";
            return;
        }

        // Zpracování nahrání souboru
        $nahledFoto = "default.jpg";
        $targetDir = __DIR__ . '../../../../public/uploads/thumbnails/';

        if (isset($_FILES['nahled_foto']) && $_FILES['nahled_foto']['error'] === UPLOAD_ERR_OK) {
            $uniqueName = basename($_FILES['nahled_foto']['name']);

            $largeDir = $targetDir . 'velke/';
            $smallDir = $targetDir . 'male/';
            $largeFilePath = $largeDir . $uniqueName;
            $smallFilePath = $smallDir . $uniqueName;

            // ✅ **Vytvoření adresářů, pokud neexistují**
            if (!is_dir($largeDir)) {
                mkdir($largeDir, 0777, true);
            }
            if (!is_dir($smallDir)) {
                mkdir($smallDir, 0777, true);
            }

            // ✅ **Přesun originálního souboru do složky "velke"**
            if (move_uploaded_file($_FILES['nahled_foto']['tmp_name'], $largeFilePath)) {
                // ✅ **Vytvoření náhledu s poměrem 3:2**
                $this->createThumbnail($largeFilePath, $smallFilePath, 300, 200);

                // ✅ **Nastavení názvu souboru do proměnné**
                $nahledFoto = $uniqueName;
                echo "<p>Fotka byla úspěšně nahrána:</p>";
                echo "<img src='/uploads/male/$nahledFoto' alt='Náhled' style='max-width: 150px;'>";
            } else {
                echo "❌ Chyba při nahrávání souboru!";
            }
        }

        $slug = $this->generateSlug($postData['title']);

        $data = [
            'nazev' => $postData['nazev'],
            'obsah' => $postData['content'],
            'viditelnost' => isset($postData['viditelnost']) ? 1 : 0,
            'nahled_foto' => $nahledFoto,
            'user_id' => $_SESSION['user_id'],
            'autor' => 1,
            'url' => $slug,
            'datum' => date('Y-m-d H:i:s')
        ];

        $result = $this->articleModel->create($data);

        if ($result) {
            header("Location: /admin/articles");
            exit;
        } else {
            echo "Chyba při ukládání článku.";
        }
    }

    public function edit($id)
    {
        $article = $this->articleModel->getById($id); // Načtení článku podle ID
        if (!$article) {
            echo "Článek nenalezen.";
            return;
        }

        $categoryModel = new Category($this->model); // Použití modelu kategorie
        $categories = $categoryModel->getAll(); // Načtení všech kategorií

        $view = '../../app/Views/Admin/articles/edit.php';
        include '../../app/Views/Admin/layout/base.php';
    }

    // Aktualizace článku
    public function update($id, $postData)
    {
        if (empty($postData['nazev'])) {
            echo "Název článku je povinný.";
            return;
        }
        if (empty($postData['content'])) {
            echo "Obsah článku je povinný.";
            return;
        }

        $targetDir = __DIR__ . '../../../../public/uploads/thumbnails/';
        $nahledFoto = $postData['current_foto']; // Použijeme aktuální foto, pokud není nové

        // Kontrola a vytvoření složky, pokud neexistuje
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (isset($_FILES['nahled_foto']) && $_FILES['nahled_foto']['error'] === UPLOAD_ERR_OK) {
            $noveFoto = basename($_FILES['nahled_foto']['name']);
            $targetFile = $targetDir . $noveFoto;

            if (move_uploaded_file($_FILES['nahled_foto']['tmp_name'], $targetFile)) {
                $nahledFoto = $noveFoto;
                echo "<p>Nová fotka byla úspěšně nahrána:</p>";
                echo "<img src='/uploads/thumbnails/$nahledFoto' alt='Náhled' style='max-width: 150px;'>";
            }
        }

        $slug = $this->generateSlug($postData['nazev']);

        $data = [
            'id' => $id,
            'nazev' => $postData['nazev'],
            'obsah' => $postData['content'],
            'viditelnost' => isset($postData['viditelnost']) ? 1 : 0,
            'nahled_foto' => $nahledFoto,
            'user_id' => $_SESSION['user_id'],
            'autor' => 1,
            'url' => $slug,
            'datum' => date('Y-m-d H:i:s')
        ];

        $result = $this->articleModel->update($data);

        if ($result) {
            header("Location: /admin/articles");
            exit;
        } else {
            echo "Chyba při aktualizaci článku.";
        }
    }

    public function delete($id)
    {
        $article = $this->articleModel->getById($id); // Získáme článek podle ID

        if (!$article) {
            die("❌ Chyba: Článek nenalezen.");
        }

        // ✅ **Smazání článku z databáze**
        if ($this->articleModel->delete($id)) {
            header("Location: /admin/articles"); // Přesměrování na seznam článků
            exit();
        } else {
            die("❌ Chyba: Článek se nepodařilo smazat.");
        }
    }

    function createThumbnail($sourcePath, $destinationPath, $thumbWidth, $thumbHeight)
    {
        list($origWidth, $origHeight, $imageType) = getimagesize($sourcePath);

        if (!function_exists('imagecreatefromjpeg')) {
            die("🔥 CHYBA: GD knihovna není aktivní! Zapni php-gd v PHP.");
        }

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }

        // Výpočet nových rozměrů pro oříznutí na 3:2
        $targetRatio = 3 / 2;
        $origRatio = $origWidth / $origHeight;

        if ($origRatio > $targetRatio) {
            // Příliš široký obrázek - ořízneme šířku
            $newWidth = (int) ($origHeight * $targetRatio);
            $newHeight = $origHeight;
            $xOffset = (int) (($origWidth - $newWidth) / 2);
            $yOffset = 0;
        } else {
            // Příliš vysoký obrázek - ořízneme výšku
            $newWidth = $origWidth;
            $newHeight = (int) ($origWidth / $targetRatio);
            $xOffset = 0;
            $yOffset = (int) (($origHeight - $newHeight) / 2);
        }

        // Vytvoření oříznutého obrázku
        $croppedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopy($croppedImage, $image, 0, 0, $xOffset, $yOffset, $newWidth, $newHeight);

        // Změna velikosti na náhled
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumbnail, $croppedImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $newWidth, $newHeight);

        // Uložení výsledného náhledu
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $destinationPath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $destinationPath);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $destinationPath);
                break;
        }

        imagedestroy($image);
        imagedestroy($croppedImage);
        imagedestroy($thumbnail);

        return true;
    }

    public function uploadImage()
    {
        $uploadDir = __DIR__ . '/../../../public/uploads/articles/';
        $publicPath = '/uploads/articles/';

        // ✅ Kontrola složky
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                error_log("❌ Nepodařilo se vytvořit složku: $uploadDir");
                http_response_code(500);
                echo json_encode(['error' => 'Nepodařilo se vytvořit složku.']);
                return;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];

            // ✅ Logování informací o souboru
            error_log("📝 Zpracovávám soubor: " . print_r($file, true));

            $fileName = uniqid() . '_' . basename($file['name']);
            $filePath = realpath($uploadDir) . DIRECTORY_SEPARATOR . $fileName; // 🔥 Převod na absolutní cestu
            $relativePath = $publicPath . $fileName;

            // ✅ Logování cílové cesty
            error_log("🛠 Cílová cesta pro obrázek: $filePath");

            // ✅ Ukládání souboru a logování úspěchu nebo chyby
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                error_log("✅ Soubor úspěšně uložen na: $filePath");
                header('Content-Type: application/json');
                echo json_encode(['location' => $relativePath]);
            } else {
                error_log("❌ Chyba při přesunu souboru do: $filePath");
                http_response_code(500);
                echo json_encode(['error' => 'Nepodařilo se přesunout soubor.']);
            }
        } else {
            error_log("❌ Neplatný požadavek nebo soubor chybí.");
            http_response_code(400);
            echo json_encode(['error' => 'Neplatný požadavek nebo soubor chybí.']);
        }
    }

    // ✅ Generování validního URL slugu z názvu
    private function generateSlug($title)
    {
        // Převod na malá písmena, odstranění diakritiky a speciálních znaků
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $slug = preg_replace('/[^a-zA-Z0-9 -]/', '', $slug);  // Povolit pouze písmena, čísla a pomlčky
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[\s-]+/', '-', $slug);  // Nahrazení mezer a vícenásobných pomlček jedinou pomlčkou

        return $slug;
    }
}
