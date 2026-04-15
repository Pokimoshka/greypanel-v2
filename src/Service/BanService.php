<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Repository\MonitorServerRepositoryInterface;
use PDO;
use PDOException;

final class BanService implements BanServiceInterface
{
    private ?array $serverConfig = null;

    public function __construct(
        private MonitorServerRepositoryInterface $serverRepo,
        private EncryptionServiceInterface $encryption
    ) {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $servers = $this->serverRepo->findAll();
        foreach ($servers as $server) {
            if (in_array($server['privilege_storage'] ?? 0, [2, 3]) && !empty($server['amxbans_db_host'])) {
                $this->serverConfig = $server;
                break;
            }
        }
    }

    public function isActive(): bool
    {
        return $this->serverConfig !== null;
    }

    private function getConnection(): ?PDO
    {
        if (!$this->isActive()) {
            return null;
        }

        $cfg = $this->serverConfig;
        $pass = $cfg['amxbans_db_pass'] ? $this->encryption->decrypt($cfg['amxbans_db_pass']) : '';

        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $cfg['amxbans_db_host'],
                $cfg['amxbans_db_name']
            );
            return new PDO($dsn, $cfg['amxbans_db_user'], $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('BanService connection error: ' . $e->getMessage());
            return null;
        }
    }

    public function getBans(int $page, int $perPage): array
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return [];
        }

        $offset = ($page - 1) * $perPage;
        $prefix = $this->serverConfig['amxbans_db_prefix'] ?? 'amx_';
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

    public function countBans(): int
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return 0;
        }
        $prefix = $this->serverConfig['amxbans_db_prefix'] ?? 'amx_';
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$prefix}bans");
        return (int)$stmt->fetchColumn();
    }

    public function searchBans(string $query): array
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return [];
        }
        $like = "%{$query}%";
        $prefix = $this->serverConfig['amxbans_db_prefix'] ?? 'amx_';
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

    public function getBanById(int $id): ?array
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return null;
        }
        $prefix = $this->serverConfig['amxbans_db_prefix'] ?? 'amx_';
        $stmt = $pdo->prepare("SELECT * FROM {$prefix}bans WHERE bid = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteBan(int $id): bool
    {
        $pdo = $this->getConnection();
        if (!$pdo) {
            return false;
        }
        $prefix = $this->serverConfig['amxbans_db_prefix'] ?? 'amx_';
        $stmt = $pdo->prepare("DELETE FROM {$prefix}bans WHERE bid = ?");
        return $stmt->execute([$id]);
    }
}