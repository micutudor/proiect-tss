<?php
namespace App\models;

use App\config\Database;
use PDO;

class Vehicle {
    private PDO $db;

    public function __construct(Database $database) {
        $this->db = $database->getConnection();
    }

    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM autovehicule");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM autovehicule WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save(array $data): array|false {
        $stmt = $this->db->prepare("
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

        return $this->find($this->db->lastInsertId());
    }

    public function update(int $id, array $data): array|false {
        $stmt = $this->db->prepare("
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

        return $this->find($id);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM autovehicule WHERE id = ?");
        $stmt->execute([$id]);
    }
}
