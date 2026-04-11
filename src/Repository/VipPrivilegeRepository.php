<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class VipPrivilegeRepository implements VipPrivilegeRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('vip_privileges');
    }

    public function findByServerId(int $serverId): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE server_id = ? ORDER BY price_per_day ASC", [$serverId]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(int $serverId, string $title, string $flags, int $pricePerDay): int
    {
        $this->db->query(
            "INSERT INTO {$this->table} (server_id, title, flags, price_per_day) VALUES (?, ?, ?, ?)",
            [$serverId, $title, $flags, $pricePerDay]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, string $title, string $flags, int $pricePerDay): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET title = ?, flags = ?, price_per_day = ? WHERE id = ?",
            [$title, $flags, $pricePerDay, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}