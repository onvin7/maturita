<?php
class Database
{
    private $connection;

    public function connect()
    {
        $this->connection = null;

        // Načtení přihlašovacích údajů, pokud nejsou definovány konstanty
        if (!defined('DB_HOST')) {
            $credentialsFile = __DIR__ . '/db_credentials.php';
            if (file_exists($credentialsFile)) {
                require_once $credentialsFile;
            }
        }

        try {
            // Použití konstant z db_credentials.php nebo fallback hodnoty (pokud soubor neexistuje)
            $host = defined('DB_HOST') ? DB_HOST : 'md413.wedos.net';
            $db_name = defined('DB_NAME') ? DB_NAME : 'd340619_blog';
            $username = defined('DB_USER') ? DB_USER : 'w340619_blog';
            $password = defined('DB_PASS') ? DB_PASS : 'kaYak714?';

            $this->connection = new PDO(
                "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
                ]
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->connection;
    }
}
