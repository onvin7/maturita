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
            SELECT clanky.id, clanky.nazev, clanky.datum, clanky.viditelnost, users.name AS autor_jmeno, users.surname AS autor_prijmeni
            FROM clanky
            LEFT JOIN users ON clanky.user_id = users.id
            ORDER BY clanky.datum DESC
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
                    WHERE clanky.id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }


    // Získání kategorií článku
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

    // Přidání kategorie k článku
    public function addCategory($articleId, $categoryId)
    {
        $query = "INSERT INTO clanky_kategorie (id_clanku, id_kategorie) VALUES (:articleId, :categoryId)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->bindParam(':categoryId', $categoryId, \PDO::PARAM_INT);
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
        $query = "DELETE FROM clanky WHERE id = :articleId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        return $stmt->execute();
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
                      autor = :autor, 
                      url = :url 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':nazev', $data['nazev']);
        $stmt->bindValue(':obsah', $data['obsah']);
        $stmt->bindValue(':datum', $data['datum']);
        $stmt->bindValue(':viditelnost', $data['viditelnost'], \PDO::PARAM_INT);
        $stmt->bindValue(':nahled_foto', $data['nahled_foto']);
        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':autor', $data['autor'], \PDO::PARAM_INT);
        $stmt->bindValue(':url', $data['url']);
        $stmt->bindValue(':id', $data['id'], \PDO::PARAM_INT);

        return $stmt->execute();
    }


    // Vytvoření nového článku
    public function create($data)
    {
        $query = "INSERT INTO clanky (nazev, obsah, viditelnost, nahled_foto, user_id, autor, url, datum)
                    VALUES (:nazev, :obsah, :viditelnost, :nahled_foto, :user_id, :autor, :url, :datum)";

        $stmt = $this->db->prepare($query);

        $stmt->bindValue(':nazev', $data['nazev'], \PDO::PARAM_STR);
        $stmt->bindValue(':obsah', $data['obsah'], \PDO::PARAM_STR);
        $stmt->bindValue(':viditelnost', $data['viditelnost'], \PDO::PARAM_INT);
        $stmt->bindValue(':nahled_foto', $data['nahled_foto'], \PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':autor', $data['autor'], \PDO::PARAM_INT);
        $stmt->bindValue(':url', $data['url'], \PDO::PARAM_STR);
        $stmt->bindValue(':datum', $data['datum'], \PDO::PARAM_STR);

        return $stmt->execute();
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
        $query = "INSERT INTO clanky (nazev, id_kategorie, datum, viditelnost, nahled_foto, obsah, autor)
                  VALUES (:title, :category, :publishDate, :isPublic, :thumbnail, :content, :showAuthor)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':publishDate', $publishDate);
        $stmt->bindParam(':isPublic', $isPublic, \PDO::PARAM_INT);
        $stmt->bindParam(':thumbnail', $thumbnail);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':showAuthor', $showAuthor, \PDO::PARAM_INT);

        if ($stmt->execute()) {
            header('Location: /admin/articles');
            exit();
        } else {
            echo "Došlo k chybě při ukládání článku.";
        }
    }

    public function getLatestArticles($limit)
    {
        $query = "SELECT id, nazev, nahled_foto, datum , url
                  FROM clanky 
                  WHERE viditelnost = 1 
                  ORDER BY datum DESC 
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByUrl($url)
    {
        $query = "SELECT * FROM clanky WHERE url = :url AND viditelnost = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':url', $url, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
}
