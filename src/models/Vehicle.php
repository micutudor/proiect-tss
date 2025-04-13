<?php
namespace App\models;

use App\config\Database;
use PDO;

class Vehicle {

    public static function all() {
        $db = Database::connect();
        $stmt = $db->query("SELECT * FROM autovehicule");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM autovehicule WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function save($data) {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO autovehicule (marca, model, data_expirare_itp, data_expirare_rovinieta, data_expirare_trusa, data_expirare_rca, numar_inmatriculare)
            VALUES (:marca, :model, :itp, :rovinieta, :trusa, :rca, :nr)
        ");
        $stmt->execute([
            ':marca' => $data['marca'],
            ':model' => $data['model'],
            ':itp' => $data['data_expirare_itp'] ?? null,
            ':rovinieta' => $data['data_expirare_rovinieta'] ?? null,
            ':trusa' => $data['data_expirare_trusa'] ?? null,
            ':rca' => $data['data_expirare_rca'] ?? null,
            ':nr' => $data['numar_inmatriculare']
        ]);

        return self::find($db->lastInsertId());
    }

    public static function update($id, $data) {
        $db = Database::connect();

        $stmt = $db->prepare("
            UPDATE autovehicule SET
                marca = :marca,
                model = :model,
                data_expirare_itp = :itp,
                data_expirare_rovinieta = :rovinieta,
                data_expirare_trusa = :trusa,
                data_expirare_rca = :rca,
                numar_inmatriculare = :nr
            WHERE id = :id
        ");

        $stmt->execute([
            ':marca' => $data['marca'],
            ':model' => $data['model'],
            ':itp' => $data['data_expirare_itp'] ?? null,
            ':rovinieta' => $data['data_expirare_rovinieta'] ?? null,
            ':trusa' => $data['data_expirare_trusa'] ?? null,
            ':rca' => $data['data_expirare_rca'] ?? null,
            ':nr' => $data['numar_inmatriculare'],
            ':id' => $id
        ]);

        return self::find($id);
    }

    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM autovehicule WHERE id = ?");
        $stmt->execute([$id]);
    }
}
