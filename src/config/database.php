<?php
namespace App\config;

use PDO;
use PDOException;

class Database {
    private $connection;

    public function __construct() {
        $host = 'localhost';
        $dbname = 'parcauto';
        $username = 'root';
        $password = '';

        try {
            $this->connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Eroare conexiune DB: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}
