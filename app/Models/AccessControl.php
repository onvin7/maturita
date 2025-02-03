<?php

namespace App\Models;

class AccessControl
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllPages()
    {
        $stmt = $this->db->prepare("SELECT page, role_1, role_2 FROM admin_access ORDER BY page");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updatePagePermissions($page, $role1, $role2)
    {
        $stmt = $this->db->prepare("
        UPDATE admin_access 
        SET role_1 = :role_1, role_2 = :role_2
        WHERE page = :page");

        $stmt->bindParam(':page', $page, \PDO::PARAM_STR);
        $stmt->bindParam(':role_1', $role1, \PDO::PARAM_INT);
        $stmt->bindParam(':role_2', $role2, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function logChange($userId, $page, $role1, $role2)
    {
        $stmt = $this->db->prepare("INSERT INTO admin_access_logs (changed_by, change_date, page, role_1, role_2) 
                                    VALUES (:changed_by, NOW(), :page, :role_1, :role_2)");
        $stmt->bindParam(':changed_by', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':page', $page, \PDO::PARAM_STR);
        $stmt->bindParam(':role_1', $role1, \PDO::PARAM_INT);
        $stmt->bindParam(':role_2', $role2, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getPagePermissions($page)
    {
        try {
            $stmt = $this->db->prepare("SELECT role_1, role_2 FROM admin_access WHERE page = :page");
            $stmt->bindParam(':page', $page, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Chyba při načítání oprávnění pro stránku $page: " . $e->getMessage());
            return false;
        }
    }

    public function addPage($page, $role1 = 1, $role2 = 1)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO admin_access (page, role_1, role_2) 
                VALUES (:page, :role_1, :role_2)
            ");
            $stmt->bindParam(':page', $page, \PDO::PARAM_STR);
            $stmt->bindParam(':role_1', $role1, \PDO::PARAM_INT);
            $stmt->bindParam(':role_2', $role2, \PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Chyba při přidávání stránky $page: " . $e->getMessage());
        }
    }

    // Získá seznam všech přístupných sekcí podle role
    public function getAccessibleSectionsByRole($role)
    {
        if ($role === 3) {
            // Superadmin má přístup ke všem stránkám
            $stmt = $this->db->query("SELECT DISTINCT page FROM admin_access");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            // Role 1 a 2 mají omezený přístup
            $stmt = $this->db->prepare("
                SELECT DISTINCT page 
                FROM admin_access 
                WHERE 
                    (role_1 = 1 AND :role = 1) OR 
                    (role_2 = 1 AND :role = 2)
            ");
            $stmt->bindValue(':role', $role, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }
    }

    public function getAccessibleSections($role)
    {
        $stmt = $this->db->prepare("SELECT DISTINCT page FROM admin_access");
        $stmt->execute();
        $allLinks = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        // Přidáme home na začátek a access-control na konec seznamu
        array_unshift($allLinks, 'home');
        $allLinks[] = 'access-control';

        // Pokud je role 3, vrátíme pouze základní odkazy (bez lomítek)
        if ($role === 3) {
            return array_filter($allLinks, function ($link) {
                return strpos($link, '/') === false; // Vrátí jen ty bez lomítka
            });
        }

        // Filtrujeme pouze odkazy, ke kterým má role přístup
        $stmt = $this->db->prepare("SELECT page FROM admin_access WHERE role_1 = :role OR role_2 = :role");
        $stmt->bindParam(':role', $role, \PDO::PARAM_INT);
        $stmt->execute();
        $accessibleLinks = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        // Vrátíme jen základní sekce bez lomítka
        return array_filter($accessibleLinks, function ($link) {
            return strpos($link, '/') === false;
        });
    }
}
