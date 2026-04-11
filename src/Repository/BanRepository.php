<?php
declare(strict_types=1);

namespace GreyPanel\Repository;

use GreyPanel\Service\SettingsServiceInterface;
use PDO;
use PDOException;

final class BanRepository implements BanRepositoryInterface
{
    private ?PDO $connection = null;
    private SettingsServiceInterface $settings;
    private array $config;

    public function __construct(SettingsServiceInterface $settings)
    {
        $this->settings = $settings;
        $this->config = [
            'host'   => $this->settings->get('amxbans_host'),
            'db'     => $this->settings->get('amxbans_db'),
            'user'   => $this->settings->get('amxbans_user'),
            'pass'   => $this->settings->get('amxbans_pass'),
            'prefix' => $this->settings->get('amxbans_prefix') ?: 'amx_',
            'active' => $this->settings->getBool('amxbans_active', false),
        ];
    }

    private function getConnection(): ?PDO
    {
        if (!$this->config['active']) {
            return null;
        }
        if ($this->connection === null) {
            try {
                $this->connection = new PDO(
                    "mysql:host={$this->config['host']};dbname={$this->config['db']};charset=utf8mb4",
                    $this->config['user'],
                    $this->config['pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
                );
            } catch (PDOException $e) {
                return null;
            }
        }
        return $this->connection;
    }

    public function findPaginated(int $page, int $perPage): array
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return [];
        }
        $offset = max(0, ($page - 1) * $perPage);
        $prefix = $this->config['prefix'];
        $sql = "SELECT b.bid, b.player_nick, b.admin_nick, b.ban_reason, b.cs_ban_reason,
                       b.ban_created, b.expired,
                       COALESCE(b.server_name, b.server_ip, 'Не указан') as server_name
                FROM {$prefix}bans b
                ORDER BY b.bid DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return 0;
        }
        $prefix = $this->config['prefix'];
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}bans");
        return (int)$stmt->fetchColumn();
    }

    public function search(string $query): array
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return [];
        }
        $like = "%{$query}%";
        $prefix = $this->config['prefix'];
        $sql = "SELECT b.bid, b.player_nick, b.admin_nick, b.ban_reason, b.cs_ban_reason,
                       b.ban_created, b.expired,
                       COALESCE(b.server_name, b.server_ip, 'Не указан') as server_name
                FROM {$prefix}bans b
                WHERE b.player_nick LIKE :nick
                   OR b.player_ip LIKE :ip
                   OR b.ban_reason LIKE :reason
                ORDER BY b.bid DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nick', $like);
        $stmt->bindValue(':ip', $like);
        $stmt->bindValue(':reason', $like);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return null;
        }
        $prefix = $this->config['prefix'];
        $stmt = $pdo->prepare("SELECT * FROM {$prefix}bans WHERE bid = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteBan(int $bid): bool
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return false;
        }
        $prefix = $this->config['prefix'];
        $stmt = $pdo->prepare("DELETE FROM {$prefix}bans WHERE bid = ?");
        return $stmt->execute([$bid]);
    }
}