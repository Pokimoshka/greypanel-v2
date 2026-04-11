<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Core\Database;

final class SettingsService implements SettingsServiceInterface
{
    private Database $db;
    private string $table;
    private array $cache = [];

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('settings');
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        $row = $this->db->fetchOne("SELECT `value` FROM {$this->table} WHERE `key` = ?", [$key]);
        $value = $row ? $row['value'] : $default;
        $this->cache[$key] = $value;
        return $value;
    }

    public function set(string $key, string $value): void
    {
        $this->db->query(
            "INSERT INTO {$this->table} (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?",
            [$key, $value, $value]
        );
        $this->cache[$key] = $value;
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int)($this->get($key) ?? $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $val = $this->get($key);
        return $val !== null ? (bool)$val : $default;
    }
}