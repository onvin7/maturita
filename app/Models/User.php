<?php

namespace App\Models;

class User
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM users ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = "INSERT INTO users (email, heslo, name, surname, role, profil_foto, zahlavi_foto, popis)
              VALUES (:email, :heslo, :name, :surname, :role, :profil_foto, :zahlavi_foto, :popis)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':email', $data['email'], \PDO::PARAM_STR);
        $stmt->bindValue(':heslo', $data['heslo'], \PDO::PARAM_STR);
        $stmt->bindValue(':name', $data['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':surname', $data['surname'], \PDO::PARAM_STR);
        $stmt->bindValue(':role', $data['role'], \PDO::PARAM_INT);
        $stmt->bindValue(':profil_foto', $data['profil_foto'], \PDO::PARAM_STR);
        $stmt->bindValue(':zahlavi_foto', $data['zahlavi_foto'], \PDO::PARAM_STR);
        $stmt->bindValue(':popis', $data['popis'], \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function update($data)
    {
        $query = "UPDATE users SET email = :email, name = :name, surname = :surname, role = :role,
              profil_foto = :profil_foto, zahlavi_foto = :zahlavi_foto, popis = :popis WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $data['id'], \PDO::PARAM_INT);
        $stmt->bindValue(':email', $data['email'], \PDO::PARAM_STR);
        $stmt->bindValue(':name', $data['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':surname', $data['surname'], \PDO::PARAM_STR);
        $stmt->bindValue(':role', $data['role'], \PDO::PARAM_INT);
        $stmt->bindValue(':profil_foto', $data['profil_foto'], \PDO::PARAM_STR);
        $stmt->bindValue(':zahlavi_foto', $data['zahlavi_foto'], \PDO::PARAM_STR);
        $stmt->bindValue(':popis', $data['popis'], \PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getByEmail($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC); // Vrátí uživatele jako pole nebo false
        } catch (\PDOException $e) {
            error_log("Chyba při načítání uživatele podle e-mailu: " . $e->getMessage());
            return false;
        }
    }

    public function getAllWithSortingAndFiltering($sortBy = 'id', $order = 'ASC', $filter = '')
    {
        $allowedColumns = ['id', 'name', 'surname', 'email', 'role'];
        $allowedOrder = ['ASC', 'DESC'];

        // Ověření sloupce a směru řazení
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'id';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'ASC';
        }

        // SQL dotaz pro filtrování a řazení
        $query = "
            SELECT * FROM users
            WHERE name LIKE :filter OR surname LIKE :filter OR email LIKE :filter
            ORDER BY $sortBy $order
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['filter' => '%' . $filter . '%']);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createUser($data)
    {
        try {
            $hashedPassword = password_hash($data['heslo'], PASSWORD_DEFAULT); // Hash hesla pro bezpečnost
            $stmt = $this->db->prepare("
                INSERT INTO users (email, heslo, role, name, surname)
                VALUES (:email, :heslo, :role, :name, :surname)
            ");
            $stmt->bindParam(':email', $data['email'], \PDO::PARAM_STR);
            $stmt->bindParam(':heslo', $hashedPassword, \PDO::PARAM_STR);
            $stmt->bindParam(':role', $data['role'], \PDO::PARAM_INT); // Výchozí role = 0
            $stmt->bindParam(':name', $data['name'], \PDO::PARAM_STR);
            $stmt->bindParam(':surname', $data['surname'], \PDO::PARAM_STR);
            return $stmt->execute(); // Vrátí true, pokud je vložení úspěšné
        } catch (\PDOException $e) {
            error_log("Chyba při vytváření uživatele: " . $e->getMessage());
            return false; // Pokud dojde k chybě, vrátí false
        }
    }

    public function checkEmailExists($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Chyba při ověřování e-mailu: " . $e->getMessage());
            return false;
        }
    }

    public function resetUserPassword($email, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET heslo = :heslo WHERE email = :email");
            $stmt->bindParam(':heslo', $hashedPassword, \PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Chyba při resetu hesla: " . $e->getMessage());
            return false;
        }
    }
}
