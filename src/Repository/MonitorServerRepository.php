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
            "INSERT INTO {$this->table} (type, ip, c_port, q_port, s_port, disabled, 
                privilege_storage, stats_engine, amxbans_db_host, amxbans_db_user, amxbans_db_pass, amxbans_db_name, amxbans_db_prefix,
                csstats_db_host, csstats_db_user, csstats_db_pass, csstats_db_name,
                aes_stats_db_host, aes_stats_db_user, aes_stats_db_pass, aes_stats_db_name,
                ftp_host, ftp_user, ftp_pass, ftp_path,
                cache, cache_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['type'], $data['ip'], $data['c_port'], $data['q_port'], $data['s_port'], $data['disabled'],
                $data['privilege_storage'] ?? 1, $data['stats_engine'] ?? 1,
                $data['amxbans_db_host'] ?? null, $data['amxbans_db_user'] ?? null, $data['amxbans_db_pass'] ?? null, $data['amxbans_db_name'] ?? null, $data['amxbans_db_prefix'] ?? null,
                $data['csstats_db_host'] ?? null, $data['csstats_db_user'] ?? null, $data['csstats_db_pass'] ?? null, $data['csstats_db_name'] ?? null,
                $data['aes_stats_db_host'] ?? null, $data['aes_stats_db_user'] ?? null, $data['aes_stats_db_pass'] ?? null, $data['aes_stats_db_name'] ?? null,
                $data['ftp_host'] ?? null, $data['ftp_user'] ?? null, $data['ftp_pass'] ?? null, $data['ftp_path'] ?? null,
                '', 0
            ]
        );
        return (int)$this->db->getPdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        // Обновляем только базовые поля, а для настроек используем отдельный метод updateSettings
        $this->db->query(
            "UPDATE {$this->table} SET type = ?, ip = ?, c_port = ?, q_port = ?, s_port = ?, disabled = ? WHERE id = ?",
            [$data['type'], $data['ip'], $data['c_port'], $data['q_port'], $data['s_port'], $data['disabled'], $id]
        );
    }

    public function updateSettings(int $id, array $settings): void
    {
        $allowed = [
            'privilege_storage', 'stats_engine',
            'amxbans_db_host', 'amxbans_db_user', 'amxbans_db_pass', 'amxbans_db_name', 'amxbans_db_prefix',
            'csstats_db_host', 'csstats_db_user', 'csstats_db_pass', 'csstats_db_name',
            'aes_stats_db_host', 'aes_stats_db_user', 'aes_stats_db_pass', 'aes_stats_db_name',
            'ftp_host', 'ftp_user', 'ftp_pass', 'ftp_path'
        ];
        $updates = [];
        $params = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowed)) {
                $updates[] = "`$key` = ?";
                $params[] = $value;
            }
        }
        if (empty($updates)) {
            return;
        }
        $params[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
        $this->db->query($sql, $params);
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