<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Core\Database;

final class VipServerRepository implements VipServerRepositoryInterface
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('vip_servers');
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY id ASC");
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO {$this->table} (type, host, user, encrypted_password, `database`, prefix, amx_id, server_name, server_ip, server_port) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['type'], $data['host'], $data['user'], $data['encrypted_password'],
                $data['database'], $data['prefix'], $data['amx_id'], $data['server_name'],
                $data['server_ip'], $data['server_port']
            ]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET type = ?, host = ?, user = ?, encrypted_password = ?, `database` = ?, prefix = ?, amx_id = ?, server_name = ?, server_ip = ?, server_port = ? WHERE id = ?",
            [
                $data['type'], $data['host'], $data['user'], $data['encrypted_password'],
                $data['database'], $data['prefix'], $data['amx_id'], $data['server_name'],
                $data['server_ip'], $data['server_port'], $id
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}