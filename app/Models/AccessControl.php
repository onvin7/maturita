<?php

namespace App\Models;

class AccessControl
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAllRoles()
    {
        $query = "SELECT * FROM admin_access ORDER BY page ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateRole($page, $role)
    {
        $query = "UPDATE admin_access SET role_required = :role WHERE page = :page";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':role', $role, \PDO::PARAM_INT);
        $stmt->bindParam(':page', $page);
        return $stmt->execute();
    }

    // Získání požadované role pro stránku
    public function getRequiredRole($page)
    {
        $query = "SELECT role_required FROM admin_access WHERE page = :page";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':page', $page);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Pokud není stránka v tabulce, vyžaduj minimální roli 1
        return $result['role_required'] ?? 1;
    }

    // Přidání nové stránky s výchozí rolí
    public function addPage($page, $defaultRole = 1)
    {
        $query = "INSERT IGNORE INTO admin_access (page, role_required) VALUES (:page, :role)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':page', $page);
        $stmt->bindParam(':role', $defaultRole, \PDO::PARAM_INT);
        return $stmt->execute();
    }
}
