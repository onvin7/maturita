<?php

namespace App\Controllers\Admin;

use App\Models\FlashNewsJSONSimple;
use App\Helpers\CsrfHelper;
use App\Helpers\LogHelper;
use Exception;

class FlashNewsJSONAdminController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new FlashNewsJSONSimple();
    }

    /**
     * Zobrazí seznam všech flash news
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $flashNews = $this->model->getAll();
            $stats = $this->model->getStats();

            $activeFlashNews = [];
            $inactiveFlashNews = [];
            foreach ($flashNews as $item) {
                if (!empty($item['is_active'])) {
                    $activeFlashNews[] = $item;
                } else {
                    $inactiveFlashNews[] = $item;
                }
            }
            $flashNews = array_merge($activeFlashNews, $inactiveFlashNews);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při načítání flash news: ' . $e->getMessage();
            $flashNews = [];
            $stats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'news_count' => 0,
                'tech_count' => 0,
                'custom_count' => 0
            ];
        }

        $csrfToken = CsrfHelper::generate();
        $adminTitle = "Správa Flash News | Admin Panel - Cyklistickey magazín";
        $view = '../app/Views/Admin/flashnews/index.php';
        include '../app/Views/Admin/layout/base.php';
    }

    /**
     * Zobrazí formulář pro vytvoření nové flash news
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $defaultSortOrder = $this->model->getNextSortOrder();
        $adminTitle = "Nová Flash News | Admin Panel - Cyklistickey magazín";
        $view = '../app/Views/Admin/flashnews/create.php';
        include '../app/Views/Admin/layout/base.php';
    }

    /**
     * Uloží novou flash news
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token';
            header('Location: /admin/flashnews/create');
            exit;
        }

        try {
            $sortOrderInput = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : null;
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'type' => $_POST['type'] ?? 'custom',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => ($sortOrderInput && $sortOrderInput > 0) ? $sortOrderInput : null,
                'created_by_name' => $_SESSION['email'] ?? 'Admin'
            ];

            if (empty($data['title'])) {
                $_SESSION['error'] = 'Název je povinný';
                header('Location: /admin/flashnews/create');
                exit;
            }

            if ($this->model->create($data)) {
                LogHelper::admin('Flash News created', 'ID: ' . ($data['id'] ?? 'new') . ', Title: ' . ($data['title'] ?? 'N/A'));
                $_SESSION['success'] = 'Flash news byla úspěšně vytvořena';
                header('Location: /admin/flashnews');
                exit;
            } else {
                LogHelper::admin('Flash News create failed', 'Title: ' . ($data['title'] ?? 'N/A'));
                $_SESSION['error'] = 'Chyba při vytváření flash news';
                header('Location: /admin/flashnews/create');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při vytváření flash news: ' . $e->getMessage();
            header('Location: /admin/flashnews/create');
            exit;
        }
    }

    /**
     * Zobrazí formulář pro úpravu flash news
     */
    public function edit()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Chybí ID flash news';
            header('Location: /admin/flashnews');
            exit;
        }

        try {
            $flashNews = $this->model->getById($id);
            if (!$flashNews) {
                $_SESSION['error'] = 'Flash news nebyla nalezena';
                header('Location: /admin/flashnews');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při načítání flash news: ' . $e->getMessage();
            header('Location: /admin/flashnews');
            exit;
        }

        $adminTitle = "Upravit Flash News | Admin Panel - Cyklistickey magazín";
        $view = '../app/Views/Admin/flashnews/edit.php';
        include '../app/Views/Admin/layout/base.php';
    }

    /**
     * Aktualizuje flash news
     */
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token';
            header('Location: /admin/flashnews');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Chybí ID flash news';
            header('Location: /admin/flashnews');
            exit;
        }

        try {
            // Načti aktuální flash news pro zjištění aktuálního typu
            $currentFlashNews = $this->model->getById($id);
            if (!$currentFlashNews) {
                $_SESSION['error'] = 'Flash news nebyla nalezena';
                header('Location: /admin/flashnews');
                exit;
            }

            $sortOrderInput = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : null;
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'type' => $_POST['type'] ?? $currentFlashNews['type'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => ($sortOrderInput && $sortOrderInput > 0) ? $sortOrderInput : null
            ];

            if (empty($data['title'])) {
                $_SESSION['error'] = 'Název je povinný';
                header('Location: /admin/flashnews/edit?id=' . $id);
                exit;
            }

            if ($this->model->update($id, $data)) {
                LogHelper::admin('Flash News updated', 'ID: ' . $id . ', Title: ' . ($data['title'] ?? 'N/A'));
                $_SESSION['success'] = 'Flash news byla úspěšně aktualizována';
                header('Location: /admin/flashnews');
                exit;
            } else {
                LogHelper::admin('Flash News update failed', 'ID: ' . $id);
                $_SESSION['error'] = 'Chyba při aktualizaci flash news';
                header('Location: /admin/flashnews/edit?id=' . $id);
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při aktualizaci flash news: ' . $e->getMessage();
            header('Location: /admin/flashnews/edit?id=' . $id);
            exit;
        }
    }

    /**
     * Smaže flash news
     */
    public function delete()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token';
            header('Location: /admin/flashnews');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Chybí ID flash news';
            header('Location: /admin/flashnews');
            exit;
        }

        try {
            if ($this->model->delete($id)) {
                LogHelper::admin('Flash News deleted', 'ID: ' . $id);
                $_SESSION['success'] = 'Flash news byla úspěšně smazána';
            } else {
                LogHelper::admin('Flash News delete failed', 'ID: ' . $id);
                $_SESSION['error'] = 'Chyba při mazání flash news';
            }
        } catch (Exception $e) {
            LogHelper::admin('Flash News delete error', 'ID: ' . $id . ', Error: ' . $e->getMessage());
            $_SESSION['error'] = 'Chyba při mazání flash news: ' . $e->getMessage();
        }

        header('Location: /admin/flashnews');
        exit;
    }

    /**
     * Přepne aktivní stav flash news
     */
    public function toggleActive()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token';
            header('Location: /admin/flashnews');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Chybí ID flash news';
            header('Location: /admin/flashnews');
            exit;
        }

        try {
            if ($this->model->toggleActive($id)) {
                $flashNews = $this->model->getById($id);
                $newStatus = $flashNews['is_active'] ? 'activated' : 'deactivated';
                LogHelper::admin('Flash News ' . $newStatus, 'ID: ' . $id);
                $_SESSION['success'] = 'Stav flash news byl změněn';
            } else {
                LogHelper::admin('Flash News toggle failed', 'ID: ' . $id);
                $_SESSION['error'] = 'Chyba při změně stavu flash news';
            }
        } catch (Exception $e) {
            LogHelper::admin('Flash News toggle error', 'ID: ' . $id . ', Error: ' . $e->getMessage());
            $_SESSION['error'] = 'Chyba při změně stavu flash news: ' . $e->getMessage();
        }

        header('Location: /admin/flashnews');
        exit;
    }

    /**
     * Aktualizuje pořadí flash news (AJAX)
     */
    public function updateSortOrder()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Neplatný CSRF token']);
            exit;
        }

        $id = $_POST['id'] ?? null;
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Chybí ID']);
            exit;
        }

        try {
            if ($this->model->updateSortOrder($id, $sortOrder)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Chyba při aktualizaci pořadí']);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Přijme nové pořadí z drag & drop / tlačítek
     */
    public function reorder()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Neplatný CSRF token']);
            exit;
        }

        $order = $_POST['order'] ?? [];
        if (!is_array($order)) {
            $order = [$order];
        }

        $order = array_map('intval', $order);
        $order = array_filter($order, fn($id) => $id > 0);

        if (empty($order)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Chybí data o pořadí']);
            exit;
        }

        try {
            if ($this->model->reorder($order)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Nepodařilo se uložit pořadí']);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Aktualizuje flash news z API
     */
    public function refresh()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Neplatný CSRF token';
            header('Location: /admin/flashnews');
            exit;
        }

        try {
            if ($this->model->refreshFromAPI()) {
                $_SESSION['success'] = 'Flash news byla úspěšně aktualizována z API';
            } else {
                $_SESSION['error'] = 'Chyba při aktualizaci z API';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při aktualizaci z API: ' . $e->getMessage();
        }

        header('Location: /admin/flashnews');
        exit;
    }
}
