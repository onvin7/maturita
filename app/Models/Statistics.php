<?php

namespace App\Models;

class Statistics
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllArticleViews()
    {
        $query = "SELECT clanky.id, clanky.nazev, SUM(views_clanku.pocet) AS total_views
                  FROM clanky
                  LEFT JOIN views_clanku ON clanky.id = views_clanku.id_clanku
                  GROUP BY clanky.id
                  ORDER BY total_views DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getArticleViewsAdmin()
    {
        $query = "SELECT clanky.id, clanky.nazev, SUM(views_clanku.pocet) AS pocet_zobrazeni
                  FROM views_clanku
                  JOIN clanky ON views_clanku.id_clanku = clanky.id
                  GROUP BY clanky.id, clanky.nazev
                  ORDER BY pocet_zobrazeni DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getPageViewsAdmin()
    {
        $query = "SELECT page, SUM(views) AS total_views FROM pageviews GROUP BY page";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function getArticleViewsById($articleId)
    {
        $query = "SELECT datum, pocet FROM views_clanku WHERE id_clanku = :articleId ORDER BY datum ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':articleId', $articleId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTopArticles($limit = 5)
    {
        $query = "SELECT clanky.id, clanky.nazev, SUM(views_clanku.pocet) AS total_views
                  FROM clanky
                  LEFT JOIN views_clanku ON clanky.id = views_clanku.id_clanku
                  GROUP BY clanky.id
                  ORDER BY total_views DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
