<?php

namespace App\Models;

use PDO;

class Ad
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Získá všechny aktivní reklamy v daném časovém rozsahu
     * 
     * @return array
     */
    public function getActiveAds()
    {
        $stmt = $this->db->query("
            SELECT r.*, u.email as user_email
            FROM reklamy r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.aktivni = 1 
            AND r.zacatek <= NOW() 
            AND r.konec >= NOW()
            ORDER BY r.vychozi DESC, r.frekvence ASC, r.vytvoreno DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Získá všechny reklamy
     * 
     * @return array
     */
    public function getAllAds()
    {
        $stmt = $this->db->query("
            SELECT r.*, u.email as user_email
            FROM reklamy r
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.vytvoreno DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Získá aktuální reklamy
     * 
     * @return array
     */
    public function getCurrentAds()
    {
        $stmt = $this->db->query("
            SELECT r.*, u.email as user_email
            FROM reklamy r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.zacatek <= NOW() AND r.konec >= NOW() 
            AND r.aktivni = 1
            ORDER BY r.vychozi DESC, r.frekvence ASC, r.vytvoreno DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Získá budoucí reklamy
     * 
     * @return array
     */
    public function getUpcomingAds()
    {
        $stmt = $this->db->query("
            SELECT r.*, u.email as user_email
            FROM reklamy r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.zacatek > NOW()
            ORDER BY r.zacatek ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Získá historické reklamy
     * 
     * @return array
     */
    public function getHistoricalAds()
    {
        $stmt = $this->db->query("
            SELECT r.*, u.email as user_email
            FROM reklamy r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.konec < NOW()
            ORDER BY r.konec DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Získá reklamu podle ID
     * 
     * @param int $id
     * @return array|false
     */
    public function getAdById($id)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.email as user_email
            FROM reklamy r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Získá výchozí reklamu
     * 
     * @return array|false
     */
    public function getDefaultAd()
    {
        $stmt = $this->db->query("
            SELECT * 
            FROM reklamy 
            WHERE vychozi = 1 
            AND aktivni = 1
            LIMIT 1
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vytvoří novou reklamu
     * 
     * @param array $data
     * @return int Identifikátor nově vytvořené reklamy
     */
    public function createAd($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO reklamy (
                nazev, obrazek, odkaz, zacatek, konec, 
                aktivni, vychozi, frekvence, user_id, vytvoreno
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['nazev'],
            $data['obrazek'],
            $data['odkaz'],
            $data['zacatek'],
            $data['konec'],
            $data['aktivni'] ?? 1,
            $data['vychozi'] ?? 0,
            $data['frekvence'] ?? 1,
            $data['user_id']
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Aktualizuje reklamu
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateAd($id, $data)
    {
        $fields = [];
        $values = [];

        if (isset($data['nazev'])) {
            $fields[] = "nazev = ?";
            $values[] = $data['nazev'];
        }
        if (isset($data['obrazek'])) {
            $fields[] = "obrazek = ?";
            $values[] = $data['obrazek'];
        }
        if (isset($data['odkaz'])) {
            $fields[] = "odkaz = ?";
            $values[] = $data['odkaz'];
        }
        if (isset($data['zacatek'])) {
            $fields[] = "zacatek = ?";
            $values[] = $data['zacatek'];
        }
        if (isset($data['konec'])) {
            $fields[] = "konec = ?";
            $values[] = $data['konec'];
        }
        if (isset($data['aktivni'])) {
            $fields[] = "aktivni = ?";
            $values[] = $data['aktivni'];
        }
        if (isset($data['vychozi'])) {
            $fields[] = "vychozi = ?";
            $values[] = $data['vychozi'];
        }
        if (isset($data['frekvence'])) {
            $fields[] = "frekvence = ?";
            $values[] = $data['frekvence'];
        }

        $fields[] = "upraveno = NOW()";
        $values[] = $id;

        $sql = "UPDATE reklamy SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Smaže reklamu
     * 
     * @param int $id
     * @return bool
     */
    public function deleteAd($id)
    {
        $stmt = $this->db->prepare("DELETE FROM reklamy WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Přepne aktivaci reklamy
     * 
     * @param int $id
     * @return bool
     */
    public function toggleActive($id)
    {
        $stmt = $this->db->prepare("
            UPDATE reklamy 
            SET aktivni = NOT aktivni, upraveno = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Nastaví reklamu jako výchozí (a zruší ostatní)
     * 
     * @param int $id
     * @return bool
     */
    public function setDefault($id)
    {
        // Nejdříve zrušíme všechny výchozí
        $this->db->exec("UPDATE reklamy SET vychozi = 0");
        
        // Pak nastavíme tuto jako výchozí
        $stmt = $this->db->prepare("
            UPDATE reklamy 
            SET vychozi = 1, upraveno = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Získá náhodnou aktivní reklamu pro zobrazení
     * 
     * @return array|false
     */
    public function getRandomActiveAd()
    {
        $ads = $this->getActiveAds();
        
        if (empty($ads)) {
            // Pokud není žádná aktivní, zkusíme výchozí
            return $this->getDefaultAd();
        }

        // Pokud je jen jedna, vrať ji
        if (count($ads) === 1) {
            return $ads[0];
        }

        // Jinak vyber náhodnou
        return $ads[array_rand($ads)];
    }
}


