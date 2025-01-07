<?php

namespace App\Models;

class Category
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll($limit = null)
    {
        $query = "
            SELECT kategorie.id, kategorie.nazev_kategorie, kategorie.url, COUNT(clanky_kategorie.id_clanku) AS pocet_clanku
            FROM kategorie
            LEFT JOIN clanky_kategorie ON clanky_kategorie.id_kategorie = kategorie.id
            LEFT JOIN clanky ON clanky.id = clanky_kategorie.id_clanku AND clanky.viditelnost = 1
            GROUP BY kategorie.id, kategorie.nazev_kategorie, kategorie.url
            ORDER BY pocet_clanku DESC
        ";

        // Pokud je nastaven limit, přidáme jej do dotazu
        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($query);

        // Pokud je nastaven limit, přiřadíme jeho hodnotu
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getById($id)
    {
        $query = "SELECT * FROM kategorie WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO kategorie (nazev_kategorie, url) VALUES (:nazev_kategorie, :url)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':nazev_kategorie', $data['nazev_kategorie'], \PDO::PARAM_STR);
        $stmt->bindValue(':url', $data['url'], \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function update($data)
    {
        $query = "UPDATE kategorie SET nazev_kategorie = :nazev_kategorie, url = :url WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':nazev_kategorie', $data['nazev_kategorie'], \PDO::PARAM_STR);
        $stmt->bindValue(':url', $data['url'], \PDO::PARAM_STR);
        $stmt->bindValue(':id', $data['id'], \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM kategorie WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getArticlesByCategory($categoryId)
    {
        $query = "
            SELECT clanky.id, clanky.nazev, clanky.nahled_foto, clanky.datum, clanky.url
            FROM clanky
            INNER JOIN clanky_kategorie ON clanky_kategorie.id_clanku = clanky.id
            WHERE clanky_kategorie.id_kategorie = :categoryId AND clanky.viditelnost = 1
            ORDER BY clanky.datum DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':categoryId', $categoryId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getByUrl($url)
    {
        $query = "SELECT * FROM kategorie WHERE url = :url";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':url', $url, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function getAllWithSortingAndFiltering($sortBy, $order, $filter)
    {
        $validSortColumns = ['nazev_kategorie', 'id']; // Povolené sloupce pro řazení
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM kategorie WHERE nazev_kategorie LIKE :filter ORDER BY $sortBy $order";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':filter', '%' . $filter . '%', \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
