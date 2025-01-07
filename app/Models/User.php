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
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllWithSortingAndFiltering($sortBy, $order, $filter)
    {
        $validSortColumns = ['name', 'surname', 'email', 'role']; // Povolené sloupce pro řazení
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'name';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT * FROM users WHERE name LIKE :filter OR surname LIKE :filter OR email LIKE :filter ORDER BY $sortBy $order";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':filter', '%' . $filter . '%', \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
