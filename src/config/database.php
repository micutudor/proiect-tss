<?php
namespace App\config;

use PDO;
use PDOException;

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbname = 'parcauto';
            $username = 'root';
            $password = '';

            try {
                self::$connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Eroare conexiune DB: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
