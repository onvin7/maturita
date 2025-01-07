<?php

class Database {
    private static $pdo;

    public static function connect() {
        if (!self::$pdo) {
            $config = require '../config/config.php';
            try {
                self::$pdo = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
                    $config['user'],
                    $config['password']
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
