<?php

namespace App\Controllers\Admin;

use PDO;
use App\Models\Promotion;
use App\Models\Article;
use App\Helpers\LogHelper;
use App\Helpers\CsrfHelper;
use DateTime;

class PromotionAdminController
{
    private $db;
    private $promotionModel;
    private $articleModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->promotionModel = new Promotion($db);
        $this->articleModel = new Article($db);
    }

    // Zobrazení stránky pro přidání propagace
    public function create()
    {
        // Načtení všech článků, změna: nezáleží, jestli už jsou propagovány
        $articles = $this->articleModel->getAll();
        $currentPromotions = $this->promotionModel->getCurrentAndFuturePromotions();

        // Aktuální datum a čas pro minimální hodnotu data v HTML
        $currentDateTime = new DateTime();
        $minDateTime = $currentDateTime->format('Y-m-d\TH:i');

        $adminTitle = "Vytvořit propagaci | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/promotions/create.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Uložení nové propagace
    public function store()
    {
        // CSRF kontrola
        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ["Neplatný bezpečnostní token (CSRF). Zkuste formulář odeslat znovu."];
            header("Location: /admin/promotions/create");
            exit();
        }

        // Kontrola, zda jsou všechny potřebné údaje přítomny
        if (
            !isset($_POST['article_id']) || !isset($_POST['start_date']) || !isset($_POST['start_time']) ||
            !isset($_POST['end_date']) || !isset($_POST['end_time'])
        ) {
            $_SESSION['errors'] = ["Všechna povinná pole musí být vyplněna."];
            header("Location: /admin/promotions/create");
            exit();
        }

        $id_clanku = $_POST['article_id'];

        // Kombinujeme datum a čas do formátu pro databázi
        $zacatek_datum = $_POST['start_date'];
        $zacatek_cas = $_POST['start_time'];
        $zacatek = $zacatek_datum . ' ' . $zacatek_cas . ':00';

        $konec_datum = $_POST['end_date'];
        $konec_cas = $_POST['end_time'];
        $konec = $konec_datum . ' ' . $konec_cas . ':00';

        $user_id = $_SESSION['user_id'];

        // Validace dat
        $errors = [];

        // Ověříme, že ID článku je platné
        if (empty($id_clanku)) {
            $errors[] = "Musíte vybrat článek pro propagaci.";
        }

        $now = new DateTime();
        $startDate = new DateTime($zacatek);
        $endDate = new DateTime($konec);

        $minAllowedTime = clone $startDate;
        $minAllowedTime->modify('+10 minutes');

        if ($now > $minAllowedTime) {
            $errors[] = "Propagace nemůže začínat dříve než za 10 minut od aktuálního času.";
        }

        // Ověříme, že konec je po začátku
        if ($endDate <= $startDate) {
            $errors[] = "Konec propagace musí být po začátku.";
        }

        // Ověříme, že se propagace nepřekrývá s jinou propagací stejného článku
        $overlappingPromotions = $this->promotionModel->getOverlappingPromotions($id_clanku, $zacatek, $konec);
        if (count($overlappingPromotions) > 0) {
            $errors[] = "Propagace se překrývá s existující propagací stejného článku.";
        }

        // Pokud jsou chyby, vrátíme uživatele zpět s chybovými zprávami
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header("Location: /admin/promotions/create");
            exit();
        }

        // Uložíme propagaci
        $this->promotionModel->createPromotion($id_clanku, $user_id, $zacatek, $konec);

        @LogHelper::admin('Promotion created', 'Article ID: ' . $id_clanku . ', Start: ' . $zacatek . ', End: ' . $konec);
        $_SESSION['success'] = "Propagace byla úspěšně vytvořena.";
        header("Location: /admin/promotions");
        exit();
    }

    // Zobrazení aktuálně propagovaných článků
    public function index()
    {
        $promotions = $this->promotionModel->getCurrentPromotions();

        $adminTitle = "Aktuální propagace | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/promotions/index.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Zobrazení budoucích propagací
    public function upcoming()
    {
        $promotions = $this->promotionModel->getUpcomingPromotions();

        $adminTitle = "Nadcházející propagace | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/promotions/upcoming.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Zobrazení historie propagací
    public function history()
    {
        $promotions = $this->promotionModel->getHistoricalPromotions();

        $adminTitle = "Historie propagací | Admin Panel - Cyklistickey magazín";

        $view = '../app/Views/Admin/promotions/history.php';
        include '../app/Views/Admin/layout/base.php';
    }

    // Smazání propagace
    public function delete($id)
    {
        // CSRF kontrola
        if (!CsrfHelper::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['errors'] = ["Neplatný bezpečnostní token (CSRF)."];
            header("Location: /admin/promotions");
            exit();
        }

        // Kontrola, zda nejde o historickou propagaci
        $promotion = $this->promotionModel->getPromotionById($id);

        if (!$promotion) {
            $_SESSION['errors'] = ["Propagace nebyla nalezena."];
            header("Location: /admin/promotions");
            exit();
        }

        $endDate = new DateTime($promotion['konec']);
        $now = new DateTime();

        // Pokud jde o historickou propagaci, zabráníme smazání
        if ($endDate < $now) {
            $_SESSION['errors'] = ["Historické propagace nelze mazat."];
            header("Location: /admin/promotions/history");
            exit();
        }

        // Jinak propagaci smažeme
        $this->promotionModel->deletePromotion($id);

        @LogHelper::admin('Promotion deleted', 'ID: ' . $id);
        $_SESSION['success'] = "Propagace byla úspěšně smazána.";
        header("Location: /admin/promotions");
        exit();
    }

    // Získání možného začátku propagace pro daný článek (API endpoint)
    public function getAvailableStartTime()
    {
        // Očekáváme AJAX request
        if (!isset($_GET['article_id'])) {
            echo json_encode(['error' => 'Chybí ID článku']);
            exit();
        }

        $articleId = $_GET['article_id'];

        // Získáme poslední propagaci článku
        $lastPromotion = $this->promotionModel->getLastPromotionForArticle($articleId);

        $result = [
            'earliest_start_time' => null
        ];

        // Pokud článek má propagaci
        if ($lastPromotion) {
            $endDate = new DateTime($lastPromotion['konec']);
            $now = new DateTime();

            // Použijeme pozdější z konec poslední propagace nebo aktuální čas
            if ($endDate > $now) {
                // Přidáme malý offset (např. 1 minutu) pro zabránění překrývání
                $endDate->modify('+1 minute');
                $result['earliest_start_time'] = $endDate->format('Y-m-d\TH:i');
            } else {
                $result['earliest_start_time'] = $now->format('Y-m-d\TH:i');
            }
        } else {
            // Pokud článek nemá propagaci, nejdřívější čas je teď
            $now = new DateTime();
            $result['earliest_start_time'] = $now->format('Y-m-d\TH:i');
        }

        echo json_encode($result);
        exit();
    }
}
