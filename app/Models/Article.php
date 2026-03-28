<?php

namespace App\Models;

class Article
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Získání všech článků
    public function getAll()
    {
        $query = "
            SELECT clanky.id, clanky.nazev, clanky.datum, clanky.viditelnost, users.name AS autor_jmeno, users.surname AS autor_prijmeni,
                  (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = clanky.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
            FROM clanky
            LEFT JOIN users ON clanky.user_id = users.id
            WHERE (clanky.viditelnost = 1 AND clanky.datum <= NOW())
               OR EXISTS (
                  SELECT 1 FROM propagace 
                  WHERE propagace.id_clanku = clanky.id 
                  AND propagace.zacatek <= NOW() 
                  AND propagace.konec >= NOW()
               )
            ORDER BY is_promoted DESC, clanky.datum DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllAdmin($limit = null)
    {
        $query = "SELECT * FROM clanky ORDER BY datum DESC";
        if ($limit) {
            $query .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($query);

        if ($limit) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Získání jednoho článku podle ID
    public function getById($id)
    {
        $query = "SELECT clanky.*, 
                        users.name AS autor_jmeno, 
                        users.surname AS autor_prijmeni, 
                        clanky_kategorie.id_kategorie
                    FROM clanky
                    LEFT JOIN users ON clanky.user_id = users.id
                    LEFT JOIN clanky_kategorie ON clanky.id = clanky_kategorie.id_clanku
                    WHERE clanky.id = :id  
                    AND (
                        (clanky.viditelnost = 1 AND clanky.datum <= NOW())
                        OR EXISTS (
                            SELECT 1 FROM propagace 
                            WHERE propagace.id_clanku = clanky.id 
                            AND propagace.zacatek <= NOW() 
                            AND propagace.konec >= NOW()
                        )
                    )";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByIdUser($userId)
    {
        $query = "SELECT DISTINCT c.id, c.nazev, c.nahled_foto, c.datum, c.url,
                   GROUP_CONCAT(DISTINCT k.nazev_kategorie) as kategorie_nazvy,
                   GROUP_CONCAT(DISTINCT k.url) as kategorie_urls,
                   (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = c.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
            FROM clanky c
            LEFT JOIN clanky_kategorie ck ON c.id = ck.id_clanku
            LEFT JOIN kategorie k ON ck.id_kategorie = k.id
            WHERE c.user_id = :userId
            AND (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
            GROUP BY c.id, c.nazev, c.nahled_foto, c.datum, c.url
            ORDER BY is_promoted DESC, c.datum DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Pro debugování
        error_log("Articles before processing: " . print_r($articles, true));

        // Zpracování kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['kategorie'] = [];
            if (!empty($article['kategorie_nazvy']) && !empty($article['kategorie_urls'])) {
                $nazvy = explode(',', $article['kategorie_nazvy']);
                $urls = explode(',', $article['kategorie_urls']);
                
                for ($i = 0; $i < count($nazvy); $i++) {
                    if (!empty($nazvy[$i]) && !empty($urls[$i])) {
                        $article['kategorie'][] = [
                            'nazev_kategorie' => trim($nazvy[$i]),
                            'url' => trim($urls[$i])
                        ];
                    }
                }
            }
            
            // Pro debugování
            error_log("Article after processing: " . print_r($article, true));
            
            // Odstraníme pomocná pole
            unset($article['kategorie_nazvy']);
            unset($article['kategorie_urls']);
        }

        return $articles;
    }

    public function getByUser($userId, $limit = null)
    {
        $query = "SELECT clanky.*, 
                        users.name AS autor_jmeno, 
                        users.surname AS autor_prijmeni,
                        GROUP_CONCAT(DISTINCT kategorie.nazev_kategorie) as kategorie_nazvy,
                        GROUP_CONCAT(DISTINCT kategorie.url) as kategorie_urls,
                        COALESCE(views.pocet, 0) as views_count,
                        (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = clanky.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
                    FROM clanky
                    LEFT JOIN users ON clanky.user_id = users.id
                    LEFT JOIN clanky_kategorie ON clanky.id = clanky_kategorie.id_clanku
                    LEFT JOIN kategorie ON clanky_kategorie.id_kategorie = kategorie.id
                    LEFT JOIN views_clanku views ON clanky.id = views.id_clanku
                    WHERE clanky.user_id = :userId
                        AND (
                            (clanky.viditelnost = 1 AND clanky.datum <= NOW())
                            OR EXISTS (
                                SELECT 1 FROM propagace 
                                WHERE propagace.id_clanku = clanky.id 
                                AND propagace.zacatek <= NOW() 
                                AND propagace.konec >= NOW()
                            )
                        )
                    GROUP BY clanky.id
                    ORDER BY is_promoted DESC, views_count DESC, clanky.datum DESC";

        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Převedení řetězců kategorií na pole
        foreach ($articles as &$article) {
            $nazvy = explode(',', $article['kategorie_nazvy'] ?? '');
            $urls = explode(',', $article['kategorie_urls'] ?? '');
            
            $article['category'] = array_map(function($nazev, $url) {
                return [
                    'nazev_kategorie' => $nazev,
                    'url_kategorie' => $url
                ];
            }, $nazvy, $urls);

            unset($article['kategorie_nazvy']);
            unset($article['kategorie_urls']);
        }

        return $articles;
    }

    // Získání kategorií článku
    public function getArticleCategories($articleId)
    {
        $query = "SELECT id_clanku, id_kategorie FROM clanky_kategorie 
                  WHERE id_clanku = :articleId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Přidání kategorií k článku
    public function addCategories($articleId, $categoryIds)
    {
        // Nejprve odstraníme všechny existující kategorie pro daný článek
        $deleteQuery = "DELETE FROM clanky_kategorie WHERE id_clanku = :articleId";
        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->execute();
        
        // Pokud není žádná kategorie vybrána, končíme
        if (empty($categoryIds)) {
            return true;
        }
        
        // Nyní přidáme vybrané kategorie
        $values = [];
        $params = [];
        
        foreach ($categoryIds as $index => $categoryId) {
            $values[] = "(:articleId, :categoryId{$index})";
            $params["articleId"] = $articleId;
            $params["categoryId{$index}"] = $categoryId;
        }
        
        $query = "INSERT INTO clanky_kategorie (id_clanku, id_kategorie) VALUES " . implode(', ', $values);
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue(":{$param}", $value, \PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }

    // Aktualizace viditelnosti článku
    public function updateVisibility($articleId, $visibility)
    {
        $query = "UPDATE clanky SET viditelnost = :visibility WHERE id = :articleId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':visibility', $visibility, \PDO::PARAM_INT);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Získání počtu zobrazení článku
    public function getViews($articleId)
    {
        $query = "SELECT pocet FROM views_clanku WHERE id_clanku = :articleId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC)['pocet'] ?? 0;
    }

    // Zvýšení počtu zobrazení článku
    public function incrementViews($articleId)
    {
        // Kontrola, zda je session aktivní, případně ji spustíme
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Vytvoříme unikátní klíč pro kontrolu návštěvy článku v daný den
        $today = date('Y-m-d');
        $sessionKey = 'article_view_' . $articleId . '_' . $today;

        // Pokud uživatel již článek dnes navštívil, nepočítáme zobrazení
        if (isset($_SESSION[$sessionKey])) {
            return true; // Článek byl již zobrazen dnešní den
        }

        // Uživatel navštívil článek poprvé v daný den, uložíme do session
        $_SESSION[$sessionKey] = true;

        // Zvýšíme počet zobrazení v databázi
        $query = "INSERT INTO views_clanku (id_clanku, pocet, datum) 
                  VALUES (:articleId, 1, CURDATE()) 
                  ON DUPLICATE KEY UPDATE pocet = pocet + 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Odstranění článku
    public function delete($articleId)
    {
        try {
            // Zahájení transakce
            $this->db->beginTransaction();

            // 1. Nejprve smažeme záznamy z views_clanku
            $query = "DELETE FROM views_clanku WHERE id_clanku = :articleId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
            $stmt->execute();

            // 2. Smažeme záznamy z clanky_kategorie
            $query = "DELETE FROM clanky_kategorie WHERE id_clanku = :articleId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
            $stmt->execute();

            // 3. Smažeme záznamy z propagace
            $query = "DELETE FROM propagace WHERE id_clanku = :articleId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
            $stmt->execute();

            // 4. Nakonec smažeme samotný článek
            $query = "DELETE FROM clanky WHERE id = :articleId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
            $stmt->execute();

            // Potvrzení transakce
            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            // Pokud nastane chyba, vrátíme změny zpět
            $this->db->rollBack();
            error_log("Chyba při mazání článku ID $articleId: " . $e->getMessage());
            return false;
        }
    }

    // Aktualizace článku
    public function update($data)
    {
        $query = "UPDATE clanky SET 
                      nazev = :nazev, 
                      obsah = :obsah, 
                      datum = :datum, 
                      viditelnost = :viditelnost, 
                      nahled_foto = :nahled_foto, 
                      user_id = :user_id, 
                      url = :url 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':nazev', $data['nazev']);
        $stmt->bindValue(':obsah', $data['obsah']);
        $stmt->bindValue(':datum', $data['datum']);
        $stmt->bindValue(':viditelnost', $data['viditelnost'], \PDO::PARAM_INT);
        $stmt->bindValue(':nahled_foto', $data['nahled_foto']);
        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':url', $data['url']);
        $stmt->bindValue(':id', $data['id'], \PDO::PARAM_INT);

        return $stmt->execute();
    }


    // Vytvoření nového článku
    public function create($data)
    {
        $query = "INSERT INTO clanky (nazev, obsah, viditelnost, nahled_foto, user_id, url, datum) 
                  VALUES (:nazev, :obsah, :viditelnost, :nahled_foto, :user_id, :url, :datum)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nazev', $data['nazev']);
        $stmt->bindParam(':obsah', $data['obsah']);
        $stmt->bindParam(':viditelnost', $data['viditelnost']);
        $stmt->bindParam(':nahled_foto', $data['nahled_foto']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':url', $data['url']);
        $stmt->bindParam(':datum', $data['datum']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function store($postData)
    {
        $title = $postData['title'];
        $category = $postData['category'];
        $publishDate = $postData['publish_date'];
        $isPublic = isset($postData['is_public']) ? 1 : 0;
        $showAuthor = isset($postData['show_author']) ? 1 : 0;
        $content = $postData['content'];
        $thumbnail = $_FILES['thumbnail']['name'] ?? null;

        // Nahrání souboru
        if ($thumbnail) {
            $targetDir = '../../uploads/thumbnails/';
            $targetFile = $targetDir . basename($thumbnail);
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile);
        }

        // Vložení dat do databáze
        $query = "INSERT INTO clanky (nazev, id_kategorie, datum, viditelnost, nahled_foto, obsah)
                    VALUES (:title, :category, :publishDate, :isPublic, :thumbnail, :content)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':publishDate', $publishDate);
        $stmt->bindParam(':isPublic', $isPublic, \PDO::PARAM_INT);
        $stmt->bindParam(':thumbnail', $thumbnail);
        $stmt->bindParam(':content', $content);

        if ($stmt->execute()) {
            header('Location: /admin/articles');
            exit();
        } else {
            echo "Došlo k chybě při ukládání článku.";
        }
    }

    public function getNewestArticle()
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   GROUP_CONCAT(k.nazev_kategorie SEPARATOR ', ') AS kategorie,
                   (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = c.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
            FROM clanky c
            LEFT JOIN clanky_kategorie ck ON c.id = ck.id_clanku
            LEFT JOIN kategorie k ON ck.id_kategorie = k.id
            WHERE (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
            GROUP BY c.id
            ORDER BY is_promoted DESC, c.datum DESC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Další 3 články s kategoriemi
    public function getLatestArticles($limit, $offset)
    {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   GROUP_CONCAT(k.nazev_kategorie) as kategorie_nazvy,
                   GROUP_CONCAT(k.id) as kategorie_ids,
                   GROUP_CONCAT(k.url) as kategorie_urls,
                   (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = c.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
            FROM clanky c
            LEFT JOIN clanky_kategorie ck ON c.id = ck.id_clanku
            LEFT JOIN kategorie k ON ck.id_kategorie = k.id
            WHERE (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
            GROUP BY c.id
            ORDER BY is_promoted DESC, c.datum DESC
            LIMIT :offset, :limit
        ");
        $stmt->bindValue(':offset', (int)$offset, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Zpracování kategorií pro každý článek
        foreach ($articles as &$article) {
            // Převedení řetězců kategorií na pole
            $article['kategorie_nazvy'] = $article['kategorie_nazvy'] ? explode(',', $article['kategorie_nazvy']) : [];
            $article['kategorie_ids'] = $article['kategorie_ids'] ? explode(',', $article['kategorie_ids']) : [];
            $article['kategorie_urls'] = $article['kategorie_urls'] ? explode(',', $article['kategorie_urls']) : [];
            
            // Vytvoření pole kategorií pro snadnější použití ve view
            $article['kategorie'] = [];
            for ($i = 0; $i < count($article['kategorie_ids']); $i++) {
                if (isset($article['kategorie_ids'][$i]) && isset($article['kategorie_nazvy'][$i]) && isset($article['kategorie_urls'][$i])) {
                    $article['kategorie'][] = [
                        'id' => $article['kategorie_ids'][$i],
                        'nazev_kategorie' => $article['kategorie_nazvy'][$i],
                        'url' => $article['kategorie_urls'][$i]
                    ];
                }
            }
            
            // Odstranění pomocných polí
            unset($article['kategorie_nazvy']);
            unset($article['kategorie_ids']);
            unset($article['kategorie_urls']);
        }
        
        return $articles;
    }

    public function getCategoriesWithArticlesSorted()
    {
        $stmt = $this->db->query("
            SELECT k.id, k.nazev_kategorie, k.url, MAX(c.datum) as posledni_clanek
            FROM kategorie k
            LEFT JOIN clanky_kategorie ck ON k.id = ck.id_kategorie
            LEFT JOIN clanky c ON ck.id_clanku = c.id
            WHERE (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
            GROUP BY k.id
            ORDER BY CASE WHEN k.nazev_kategorie = 'Nevybráno' THEN 1 ELSE 0 END, posledni_clanek DESC
        ");

        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($categories as &$category) {
            $articlesStmt = $this->db->prepare("
                SELECT c.*,
                       (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = c.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
                FROM clanky c
                LEFT JOIN clanky_kategorie ck ON c.id = ck.id_clanku
                WHERE ck.id_kategorie = :id 
                AND (
                    (c.viditelnost = 1 AND c.datum <= NOW())
                    OR EXISTS (
                        SELECT 1 FROM propagace 
                        WHERE propagace.id_clanku = c.id 
                        AND propagace.zacatek <= NOW() 
                        AND propagace.konec >= NOW()
                    )
                )
                ORDER BY is_promoted DESC, c.datum DESC
                LIMIT 3
            ");
            $articlesStmt->execute(['id' => $category['id']]);
            $category['articles'] = $articlesStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        return $categories;
    }
    

    public function getByUrl($url)
    {
        $query = "SELECT c.*, GROUP_CONCAT(k.nazev_kategorie) as kategorie_nazvy, 
                         GROUP_CONCAT(k.id) as kategorie_ids,
                         GROUP_CONCAT(k.url) as kategorie_urls
                  FROM clanky c 
                  LEFT JOIN clanky_kategorie ck ON c.id = ck.id_clanku
                  LEFT JOIN kategorie k ON ck.id_kategorie = k.id
                  WHERE c.url = :url 
                  AND (
                      (c.viditelnost = 1 AND c.datum <= NOW())
                      OR EXISTS (
                          SELECT 1 FROM propagace 
                          WHERE propagace.id_clanku = c.id 
                          AND propagace.zacatek <= NOW() 
                          AND propagace.konec >= NOW()
                      )
                  )
                  GROUP BY c.id";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':url', $url, \PDO::PARAM_STR);
        $stmt->execute();
        
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($article) {
            // Převedení řetězců kategorií na pole
            $article['kategorie_nazvy'] = $article['kategorie_nazvy'] ? explode(',', $article['kategorie_nazvy']) : [];
            $article['kategorie_ids'] = $article['kategorie_ids'] ? explode(',', $article['kategorie_ids']) : [];
            $article['kategorie_urls'] = $article['kategorie_urls'] ? explode(',', $article['kategorie_urls']) : [];
            
            // Vytvoření pole kategorií pro snadnější použití ve view
            $article['kategorie'] = [];
            for ($i = 0; $i < count($article['kategorie_ids']); $i++) {
                $article['kategorie'][] = [
                    'id' => $article['kategorie_ids'][$i],
                    'nazev_kategorie' => $article['kategorie_nazvy'][$i],
                    'url' => $article['kategorie_urls'][$i]
                ];
            }
            
            // Odstranění pomocných polí
            unset($article['kategorie_nazvy']);
            unset($article['kategorie_ids']);
            unset($article['kategorie_urls']);
        }
        
        return $article;
    }

    public function getAllWithSortingAndFiltering($sortBy, $order, $filter)
    {
        $validSortColumns = ['id', 'nazev', 'datum', 'viditelnost', 'user_id', 'pocet_zobrazeni'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'datum';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT clanky.*, 
                    users.name AS autor_jmeno, 
                    users.surname AS autor_prijmeni, 
                    SUM(views_clanku.pocet) AS pocet_zobrazeni 
            FROM clanky
            LEFT JOIN users ON clanky.user_id = users.id
            LEFT JOIN views_clanku ON clanky.id = views_clanku.id_clanku
            WHERE clanky.nazev LIKE :filter
            GROUP BY clanky.id, users.name, users.surname
            ORDER BY $sortBy $order";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':filter', '%' . $filter . '%', \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function slugExists($slug, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM articles WHERE url = :slug";
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function saveArticleAudio($articleId, $audioPath)
    {
        $query = "UPDATE clanky SET audio = :audio_path WHERE id = :article_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':audio_path' => $audioPath,
            ':article_id' => $articleId
        ]);
    }

    public function getRelatedArticles($articleId, $limit = 3)
    {
        $query = "SELECT DISTINCT c.id, c.nazev, c.nahled_foto, c.datum, c.url,
                   GROUP_CONCAT(k.nazev_kategorie) as kategorie_nazvy,
                   GROUP_CONCAT(k.url) as kategorie_urls
            FROM clanky c
            INNER JOIN clanky_kategorie ck1 ON c.id = ck1.id_clanku
            LEFT JOIN clanky_kategorie ck2 ON c.id = ck2.id_clanku
            LEFT JOIN kategorie k ON ck2.id_kategorie = k.id
            WHERE ck1.id_kategorie IN (
                SELECT id_kategorie 
                FROM clanky_kategorie 
                WHERE id_clanku = :articleId
            )
            AND c.id != :articleId
            AND (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
            GROUP BY c.id
            ORDER BY c.datum DESC
            LIMIT 10";  // Nejdřív vybereme 10 nejnovějších

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Zpracování kategorií pro každý článek
        foreach ($articles as &$article) {
            $article['kategorie'] = [];
            if ($article['kategorie_nazvy'] && $article['kategorie_urls']) {
                $nazvy = explode(',', $article['kategorie_nazvy']);
                $urls = explode(',', $article['kategorie_urls']);
                
                for ($i = 0; $i < count($nazvy); $i++) {
                    $article['kategorie'][] = [
                        'nazev_kategorie' => $nazvy[$i],
                        'url' => $urls[$i]
                    ];
                }
            }
            
            // Odstraníme pomocná pole
            unset($article['kategorie_nazvy']);
            unset($article['kategorie_urls']);
        }

        // Náhodně vybereme 3 články z těch 10
        shuffle($articles);
        return array_slice($articles, 0, $limit);
    }

    public function getAllWithAuthors()
    {
        $sql = "SELECT c.*, u.name as author_name, u.surname as author_surname,
                      (SELECT COUNT(*) FROM propagace WHERE propagace.id_clanku = c.id AND propagace.zacatek <= NOW() AND propagace.konec >= NOW()) AS is_promoted
                FROM clanky c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE (
                    (c.viditelnost = 1 AND c.datum <= NOW())
                    OR EXISTS (
                        SELECT 1 FROM propagace 
                        WHERE propagace.id_clanku = c.id 
                        AND propagace.zacatek <= NOW() 
                        AND propagace.konec >= NOW()
                    )
                )
                ORDER BY is_promoted DESC, c.datum DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Získání článků z posledních 7 dnů
    public function getLastWeekArticles()
    {
        $query = "SELECT clanky.*, users.name AS autor_jmeno, users.surname AS autor_prijmeni
                  FROM clanky
                  LEFT JOIN users ON clanky.user_id = users.id
                  WHERE clanky.datum >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  AND clanky.viditelnost = 1
                  ORDER BY clanky.datum DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Aktualizace informace o audio souboru u článku
    public function updateAudioField($articleId, $audioFilename)
    {
        $query = "UPDATE clanky SET audio_file = :audioFile WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':audioFile', $audioFilename);
        $stmt->bindParam(':id', $articleId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Získání názvů kategorií článku (pro zpětnou kompatibilitu)
    public function getCategories($articleId)
    {
        $query = "SELECT kategorie.nazev_kategorie FROM clanky_kategorie 
                  JOIN kategorie ON clanky_kategorie.id_kategorie = kategorie.id 
                  WHERE clanky_kategorie.id_clanku = :articleId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // Přidání kategorie k článku (pro zpětnou kompatibilitu)
    public function addCategory($articleId, $categoryId)
    {
        $query = "INSERT INTO clanky_kategorie (id_clanku, id_kategorie) VALUES (:articleId, :categoryId)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $categoryId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function search($query, ?int $limit = null, ?int $offset = null)
    {
        $sql = "
            SELECT c.*,
                   u.name AS autor_jmeno, 
                   u.surname AS autor_prijmeni,
                   GROUP_CONCAT(DISTINCT k.nazev_kategorie) as kategorie_nazvy,
                   GROUP_CONCAT(DISTINCT k.url) as kategorie_urls,
                   CASE
                       WHEN c.nazev LIKE :query THEN 1
                       ELSE 2
                   END as relevance
            FROM clanky c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN clanky_kategorie ck ON c.id = ck.id_clanku
            LEFT JOIN kategorie k ON ck.id_kategorie = k.id
            WHERE (c.nazev LIKE :query OR c.obsah LIKE :query)
            AND (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
            GROUP BY c.id
            ORDER BY relevance ASC, c.datum DESC
        ";

        if ($limit !== null) {
            $limitValue = max(1, (int) $limit);
            $offsetValue = max(0, (int) ($offset ?? 0));
            $sql .= " LIMIT {$limitValue} OFFSET {$offsetValue}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->execute();
        
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Process categories
        foreach ($articles as &$article) {
            $article['kategorie'] = [];
            if (!empty($article['kategorie_nazvy']) && !empty($article['kategorie_urls'])) {
                $nazvy = explode(',', $article['kategorie_nazvy']);
                $urls = explode(',', $article['kategorie_urls']);
                
                for ($i = 0; $i < count($nazvy); $i++) {
                    if (!empty($nazvy[$i]) && !empty($urls[$i])) {
                        $article['kategorie'][] = [
                            'nazev_kategorie' => trim($nazvy[$i]),
                            'url' => trim($urls[$i])
                        ];
                    }
                }
            }
            unset($article['kategorie_nazvy']);
            unset($article['kategorie_urls']);
        }

        return $articles;
    }

    public function searchCount($query): int
    {
        $sql = "
            SELECT COUNT(DISTINCT c.id) as total
            FROM clanky c
            WHERE (c.nazev LIKE :query OR c.obsah LIKE :query)
            AND (
                (c.viditelnost = 1 AND c.datum <= NOW())
                OR EXISTS (
                    SELECT 1 FROM propagace 
                    WHERE propagace.id_clanku = c.id 
                    AND propagace.zacatek <= NOW() 
                    AND propagace.konec >= NOW()
                )
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }
}
