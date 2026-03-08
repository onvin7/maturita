<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Helpers\CsrfHelper;
use App\Helpers\LogHelper;

use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatefromgif;

class UserAdminController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new User($db);
    }

    // Zobrazení seznamu uživatelů
    public function index()
    {
        $sortBy = $_GET['sort_by'] ?? 'id';      // Výchozí řazení podle ID
        $order = $_GET['order'] ?? 'ASC';       // Výchozí vzestupné řazení
        $filter = $_GET['filter'] ?? '';        // Výchozí bez filtru

        // Načtení uživatelů s filtrováním a řazením
        $users = $this->model->getAllWithSortingAndFiltering($sortBy, $order, $filter);

        $adminTitle = "Uživatelé | Admin Panel - Cyklistickey magazín";

        // Zobrazení view
        $view = '../app/Views/Admin/users/index.php';
        include '../app/Views/Admin/layout/base.php';
    }


    public function edit($id)
    {
        // Načtení uživatele z databáze
        $user = $this->model->getById($id);

        // Kontrola, zda byl uživatel nalezen
        if (!$user) {
            echo "Uživatel nenalezen.";
            return;
        }

        $adminTitle = "Upravit uživatele: " . $user['name'] . " " . $user['surname'] . " | Admin Panel - Cyklistickey magazín";

        // Zahrnutí šablony pro úpravu uživatele
        $view = '../app/Views/Admin/users/edit.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function update($id, $postData)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!\App\Helpers\CsrfHelper::verify($postData['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token. Zkuste to prosím znovu.';
            header("Location: /admin/users/edit/{$id}");
            exit;
        }

        $existingUser = $this->model->getById($id);
        if (!$existingUser) {
            $_SESSION['error'] = 'Uživatel nebyl nalezen.';
            header("Location: /admin/users");
            exit;
        }

        $email = trim($postData['email'] ?? '');
        $name = trim($postData['name'] ?? '');
        $surname = trim($postData['surname'] ?? '');

        if ($email === '' || $name === '' || $surname === '') {
            $_SESSION['error'] = 'E-mail, jméno a příjmení jsou povinné.';
            header("Location: /admin/users/edit/{$id}");
            exit;
        }

        $role = isset($postData['role']) ? (int)$postData['role'] : (int)$existingUser['role'];
        $role = max(0, min(3, $role));

        $public_visible = isset($postData['public_visible']) && $postData['public_visible'] == '1' ? 1 : 0;

        $data = [
            'id' => (int)$id,
            'email' => $email,
            'name' => $name,
            'surname' => $surname,
            'role' => $role,
            'public_visible' => $public_visible,
            'profil_foto' => $existingUser['profil_foto'] ?? null,
            'popis' => $postData['popis'] ?? $existingUser['popis'] ?? ''
        ];

        if ($this->model->update($data)) {
            LogHelper::admin('User updated', 'ID: ' . $id . ', Email: ' . $email . ', Role: ' . $role);
            $_SESSION['success'] = 'Uživatel byl úspěšně aktualizován.';
            header("Location: /admin/users");
            exit;
        }

        LogHelper::admin('User update failed', 'ID: ' . $id);
        $_SESSION['error'] = 'Chyba při aktualizaci uživatele.';
        header("Location: /admin/users/edit/{$id}");
        exit;
    }

    public function delete($id)
    {
        // CSRF kontrola
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token.';
            header("Location: /admin/users");
            exit;
        }

        $result = $this->model->delete($id); // Volání metody `delete` v modelu

        if ($result) {
            LogHelper::admin('User deleted', 'ID: ' . $id);
            header("Location: /admin/users");
            exit;
        } else {
            LogHelper::admin('User delete failed', 'ID: ' . $id);
            echo "Chyba při mazání uživatele.";
        }
    }



    public function settings()
    {
        $userId = $_SESSION['user_id'];

        $user = $this->model->getById($userId);
        $social_links = $this->model->getSocials($userId);
        $available_socials = $this->model->getAvailableSocialSites();

        $adminTitle = "Nastavení uživatele | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/users/settings.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function saveSocialSites()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF kontrola
            if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Neplatný CSRF token.';
                header('Location: /admin/settings');
                exit;
            }

            $userId = $_SESSION['user_id'];
            
            // First, delete all existing social links for this user
            $this->model->deleteUserSocialLinks($userId);
            
            // Then save the new ones
            if (isset($_POST['social_id']) && isset($_POST['link'])) {
                foreach ($_POST['social_id'] as $key => $socialId) {
                    if (!empty($socialId) && !empty($_POST['link'][$key])) {
                        $this->model->saveUserSocialLink($userId, $socialId, $_POST['link'][$key]);
                    }
                }
            }
            
            header('Location: /admin/settings?success=1');
        } else {
            header('Location: /admin/settings');
        }
    }

    public function updateSettings()
    {
        // CSRF kontrola
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token.';
            header('Location: /admin/settings');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $description = $_POST['description'];

        error_log("DEBUG: Příchozí soubory: " . print_r($_FILES, true));

        // **Profilová fotka**
        $profile_photo = isset($_POST['current_foto']) ? $_POST['current_foto'] : null;

        if (isset($_FILES['profil_foto']) && $_FILES['profil_foto']['error'] === UPLOAD_ERR_OK) {
            $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/users/thumbnails/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['profil_foto']['name']);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['profil_foto']['tmp_name'], $targetFile)) {
                error_log("✅ Profilová fotka nahrána do: $targetFile");
                @LogHelper::admin('User profile photo uploaded', 'User ID: ' . $userId . ', File: ' . $fileName . ', Size: ' . $_FILES['profil_foto']['size'] . ' bytes');
                $this->resizeAndCropImage($targetFile, 400, 400, true);
                $profile_photo = $fileName;
            } else {
                error_log("✖️ Chyba při přesunu profilové fotky.");
            }
        }

        // **Aktualizace databáze pouze s novými hodnotami**
        $this->model->updateUser($userId, $name, $surname, $email, $description, $profile_photo);
        @LogHelper::admin('User settings updated', 'User ID: ' . $userId . ', Email: ' . $email);

        // Zpracování sociálních sítí
        $social_ids = isset($_POST['social_id']) && is_array($_POST['social_id']) ? $_POST['social_id'] : [];
        $links = isset($_POST['link']) && is_array($_POST['link']) ? $_POST['link'] : [];

        // Načíst všechny existující sociální sítě uživatele
        $existingSocials = $this->model->getSocials($userId);
        $existingSocialIds = [];
        foreach ($existingSocials as $existing) {
            $existingSocialIds[$existing['social_id']] = $existing;
        }

        // Zpracovat všechny odeslané sociální sítě
        $submittedSocialIds = [];
        
        // Projít všechny odeslané hodnoty
        foreach ($social_ids as $key => $socialId) {
            if (!empty($socialId) && isset($links[$key])) {
                $link = trim($links[$key]);
                if (!empty($link)) {
                    $submittedSocialIds[] = $socialId;
                    
                    // Pokud už existuje, aktualizovat
                    if (isset($existingSocialIds[$socialId])) {
                        $this->model->updateUserSocialLink($userId, $socialId, $link);
                    } else {
                        // Pokud neexistuje, vytvořit nový
                        $this->model->saveUserSocialLink($userId, $socialId, $link);
                    }
                }
            }
        }

        // Smazat ty, které už nejsou v POST (uživatel je odstranil)
        foreach ($existingSocialIds as $socialId => $existing) {
            if (!in_array($socialId, $submittedSocialIds)) {
                $this->model->deleteUserSocialLink($userId, $socialId);
            }
        }

        $_SESSION['success'] = "Nastavení bylo úspěšně aktualizováno.";
        header("Location: /admin/settings");
        exit();
    }

    private function resizeAndCropImage($filePath, $maxWidth, $maxHeight, $isSquare)
    {
        list($originalWidth, $originalHeight, $imageType) = getimagesize($filePath);

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }

        if ($isSquare) {
            // **Ořez na čtverec podle menší strany**
            $size = min($originalWidth, $originalHeight);
            $srcX = ($originalWidth - $size) / 2;
            $srcY = ($originalHeight - $size) / 2;
            $croppedImage = imagecreatetruecolor($size, $size);
            imagecopyresampled($croppedImage, $source, 0, 0, $srcX, $srcY, $size, $size, $size, $size);
        } else {
            // **Ořez na 16:9, pokud je větší než limit, zmenšit**
            $targetWidth = min($originalWidth, $maxWidth);
            $targetHeight = ($targetWidth / 16) * 9;

            if ($targetHeight > $originalHeight) {
                $targetHeight = min($originalHeight, $maxHeight);
                $targetWidth = ($targetHeight / 9) * 16;
            }

            $srcX = ($originalWidth - $targetWidth) / 2;
            $srcY = ($originalHeight - $targetHeight) / 2;
            $croppedImage = imagecreatetruecolor($targetWidth, $targetHeight);
            imagecopyresampled($croppedImage, $source, 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $targetWidth, $targetHeight);
        }

        // **Změna velikosti na maximální rozměr**
        $resizedImage = imagecreatetruecolor($maxWidth, $maxHeight);
        imagecopyresampled($resizedImage, $croppedImage, 0, 0, 0, 0, $maxWidth, $maxHeight, imagesx($croppedImage), imagesy($croppedImage));

        // **Uložit obrázek zpět**
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($resizedImage, $filePath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($resizedImage, $filePath);
                break;
            case IMAGETYPE_GIF:
                imagegif($resizedImage, $filePath);
                break;
        }

        imagedestroy($source);
        imagedestroy($croppedImage);
        imagedestroy($resizedImage);
    }

    public function socialSites()
    {
        // Načteme všechny dostupné sociální sítě, ne konkrétního uživatele
        $socials = $this->model->getAvailableSocialSites();
        
        $adminTitle = "Správa sociálních sítí | Admin Panel - Cyklistickey magazín";
        
        $view = '../app/Views/Admin/users/social_sites.php';
        include '../app/Views/Admin/layout/base.php';
    }

    public function saveSocialSite()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF kontrola
            if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Neplatný CSRF token.';
                header('Location: /admin/social-sites');
                exit;
            }

            $nazev = trim($_POST['nazev'] ?? '');
            $fa_class = trim($_POST['fa_class'] ?? '');

            if (empty($nazev) || empty($fa_class)) {
                $_SESSION['error'] = 'Název a Font Awesome třída jsou povinné.';
                header('Location: /admin/social-sites');
                exit;
            }

            $result = $this->model->createSocialSite($nazev, $fa_class);

            if ($result) {
                LogHelper::admin('Social site created', 'Název: ' . $nazev);
                $_SESSION['success'] = 'Sociální síť byla úspěšně přidána.';
            } else {
                $_SESSION['error'] = 'Chyba při přidávání sociální sítě.';
            }

            header('Location: /admin/social-sites');
            exit;
        } else {
            header('Location: /admin/social-sites');
            exit;
        }
    }

    public function deleteSocialSite($id)
    {
        // CSRF kontrola
        if (!\App\Helpers\CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token.';
            header('Location: /admin/social-sites');
            exit;
        }

        if (!is_numeric($id)) {
            $_SESSION['error'] = 'Neplatné ID.';
            header('Location: /admin/social-sites');
            exit;
        }

        // Kontrola, zda někdo tuto síť nepoužívá
        $usersUsing = $this->model->getUsersUsingSocialSite($id);
        if (!empty($usersUsing)) {
            $_SESSION['error'] = 'Tuto sociální síť používají někteří uživatelé. Nejprve ji odstraňte z jejich profilů.';
            header('Location: /admin/social-sites');
            exit;
        }

        $result = $this->model->deleteSocialSite($id);

        if ($result) {
            LogHelper::admin('Social site deleted', 'ID: ' . $id);
            $_SESSION['success'] = 'Sociální síť byla úspěšně smazána.';
        } else {
            $_SESSION['error'] = 'Chyba při mazání sociální sítě.';
        }

        header('Location: /admin/social-sites');
        exit;
    }
}

