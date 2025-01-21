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

    public function getPagePermissions($page)
    {
        $stmt = $this->db->prepare("SELECT role_1, role_2 FROM admin_access WHERE page = :page");
        $stmt->bindParam(':page', $page);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Přidání nové stránky s výchozími oprávněními
    public function addPage($page, $role1, $role2)
    {
        // Seznam stránek, které nemají být zapisovány do databáze
        $excludedPages = ['access-control', 'access-control/update', 'logout', ''];

        // Pokud je stránka v seznamu vyloučených, nic nezapisujeme
        foreach ($excludedPages as $excludedPage) {
            if (strpos($page, $excludedPage) === 0) {
                return;
            }
        }

        // Extrakce pouze prvních dvou částí URI
        $segments = explode('/', $page);
        $normalizedPage = isset($segments[0], $segments[1]) ? $segments[0] . '/' . $segments[1] : $page;

        $stmt = $this->db->prepare(
            "INSERT INTO admin_access (page, role_1, role_2) VALUES (:page, :role_1, :role_2)"
        );
        $stmt->bindParam(':page', $normalizedPage, \PDO::PARAM_STR);
        $stmt->bindParam(':role_1', $role1, \PDO::PARAM_INT);
        $stmt->bindParam(':role_2', $role2, \PDO::PARAM_INT);
        $stmt->execute();

        // Záznam do admin_access_logs
        $currentDate = date('Y-m-d H:i:s');
        $logStmt = $this->db->prepare(
            "INSERT INTO admin_access_logs (changed_by, change_date, page, role_1, role_2) VALUES (:changed_by, :change_date, :page, :role_1, :role_2)"
        );
        $logStmt->bindParam(':changed_by', $_SESSION['user_id'], \PDO::PARAM_INT); // Předpoklad: uživatel je v session
        $logStmt->bindParam(':change_date', $currentDate, \PDO::PARAM_STR);
        $logStmt->bindParam(':page', $normalizedPage, \PDO::PARAM_STR);
        $logStmt->bindParam(':role_1', $role1, \PDO::PARAM_INT);
        $logStmt->bindParam(':role_2', $role2, \PDO::PARAM_INT);
        $logStmt->execute();
    }
}
