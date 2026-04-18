<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Core\Database;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class SettingsService implements SettingsServiceInterface
{
    private Database $db;
    private string $table;
    private FilesystemAdapter $cache;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->table('settings');
        $this->cache = new FilesystemAdapter('settings', 0, __DIR__ . '/../../var/cache');
    }

    public function get(string $key, ?string $default = null): ?string
    {
        // Пытаемся получить из кэша
        $cached = $this->cache->get($key, function (ItemInterface $item) use ($key, $default) {
            $item->expiresAfter(3600); // на 1 час
            $row = $this->db->fetchOne("SELECT `value` FROM {$this->table} WHERE `key` = ?", [$key]);
            return $row ? $row['value'] : $default;
        });
        return $cached;
    }

    public function set(string $key, string $value): void
    {
        $this->db->query(
            "INSERT INTO {$this->table} (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?",
            [$key, $value, $value]
        );
        // Сбрасываем кэш для этого ключа
        $this->cache->delete($key);
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