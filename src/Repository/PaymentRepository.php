<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class PaymentRepository implements PaymentRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('payments');
    }

    public function add(int $userId, string $system, int $amount, string $externalId, int $status = 0): int
    {
        $now = time();
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, system, amount, external_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $system, $amount, $externalId, $status, $now]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function findByExternalId(string $externalId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE external_id = ?", [$externalId]);
    }

    public function updateStatus(string $externalId, int $status): void
    {
        $this->db->query("UPDATE {$this->table} SET status = ? WHERE external_id = ?", [$status, $externalId]);
    }
}