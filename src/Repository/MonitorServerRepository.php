<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class MonitorServerRepository implements MonitorServerRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('monitor_servers');
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY id ASC");
    }

    public function findEnabled(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE disabled = 0 ORDER BY id ASC");
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO {$this->table} (type, ip, c_port, q_port, s_port, disabled, cache, cache_time) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['type'], $data['ip'], $data['c_port'], $data['q_port'],
                $data['s_port'], $data['disabled'], '', 0
            ]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET type = ?, ip = ?, c_port = ?, q_port = ?, s_port = ?, disabled = ? WHERE id = ?",
            [$data['type'], $data['ip'], $data['c_port'], $data['q_port'], $data['s_port'], $data['disabled'], $id]
        );
    }

    public function updateStatus(int $id, int $status, string $cache, int $cacheTime): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET status = ?, cache = ?, cache_time = ? WHERE id = ?",
            [$status, $cache, $cacheTime, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}