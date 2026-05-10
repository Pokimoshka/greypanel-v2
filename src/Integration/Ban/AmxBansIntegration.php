<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Ban;

use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Ban;
use PDO;
use PDOException;

class AmxBansIntegration implements BanSystemIntegration
{
    private ?PDO $pdo = null;
    /** @var array<string, mixed> */
    private array $config = [];

    public function __construct(
        private MonitorServerRepositoryInterface $serverRepo,
        private EncryptionServiceInterface $encryption
    ) {
    }

    private function initConnection(): void
    {
        if ($this->pdo !== null) {
            return;
        }

        $servers = $this->serverRepo->findAll();
        foreach ($servers as $server) {
            if (!empty($server['banlist_db_host']) && in_array($server['privilege_storage'] ?? 0, [2,3])) {
                $this->config = $server;
                break;
            }
        }

        if (empty($this->config)) {
            return;
        }

        $pass = $this->config['banlist_db_pass'] ? $this->encryption->decrypt($this->config['banlist_db_pass']) : '';
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=utf8mb4",
            $this->config['banlist_db_host'],
            $this->config['banlist_db_name']
        );
        try {
            $this->pdo = new PDO($dsn, $this->config['banlist_db_user'], $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('banlist connection failed: ' . $e->getMessage());
        }
    }

    public function isConnected(): bool
    {
        $this->initConnection();
        return $this->pdo !== null;
    }

    public function getBans(int $page, int $perPage, ?string $search = null, ?int $statusFilter = null): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        $prefix = $this->config['banlist_db_prefix'] ?? 'amx_';
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT b.bid, b.player_nick, b.admin_nick, b.ban_reason, b.cs_ban_reason,
                    b.ban_created, b.ban_length, b.expired, b.server_name,
                    b.player_ip, b.admin_id, b.player_id
                FROM {$prefix}bans b";

        $conditions = [];
        $params = [];

        if ($statusFilter !== null) {
            switch ($statusFilter) {
                case Ban::STATUS_ACTIVE:
                    $conditions[] = "(b.expired = 0 AND (b.ban_length = 0 OR (b.ban_created + b.ban_length) > UNIX_TIMESTAMP()))";
                    break;
                case Ban::STATUS_EXPIRED:
                    $conditions[] = "(b.expired = 1 AND (b.ban_length > 0 AND (b.ban_created + b.ban_length) <= UNIX_TIMESTAMP()))";
                    break;
                case Ban::STATUS_UNBANNED:
                    $conditions[] = "(b.expired = 1 AND b.ban_reason NOT LIKE '%[PAID]%')";
                    break;
                case Ban::STATUS_BOUGHT_UNBAN:
                    $conditions[] = "b.expired = 1 AND b.ban_reason LIKE '%[PAID]%'";
                    break;
            }
        }

        if ($search) {
            $like = "%{$search}%";
            $conditions[] = "(b.player_nick LIKE ? OR b.player_ip LIKE ? OR b.ban_reason LIKE ?)";
            $params = array_merge($params, [$like, $like, $like]);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY b.bid DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(fn ($row) => new Ban($row), $rows);
    }

    public function countBans(?string $search = null, ?int $statusFilter = null): int
    {
        if (!$this->isConnected()) {
            return 0;
        }
        $prefix = $this->config['banlist_db_prefix'] ?? 'amx_';

        $sql = "SELECT COUNT(*) FROM {$prefix}bans b";
        $conditions = [];
        $params = [];

        if ($statusFilter !== null) {
            switch ($statusFilter) {
                case Ban::STATUS_ACTIVE:
                    $conditions[] = "(b.expired = 0 AND (b.ban_length = 0 OR (b.ban_created + b.ban_length) > UNIX_TIMESTAMP()))";
                    break;
                case Ban::STATUS_EXPIRED:
                    $conditions[] = "(b.expired = 1 AND (b.ban_length > 0 AND (b.ban_created + b.ban_length) <= UNIX_TIMESTAMP()))";
                    break;
                case Ban::STATUS_UNBANNED:
                    $conditions[] = "(b.expired = 1 AND b.ban_reason NOT LIKE '%[PAID]%')";
                    break;
                case Ban::STATUS_BOUGHT_UNBAN:
                    $conditions[] = "b.expired = 1 AND b.ban_reason LIKE '%[PAID]%'";
                    break;
            }
        }

        if ($search) {
            $like = "%{$search}%";
            $conditions[] = "(b.player_nick LIKE ? OR b.player_ip LIKE ? OR b.ban_reason LIKE ?)";
            $params = array_merge($params, [$like, $like, $like]);
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getBanById(int $id): ?Ban
    {
        if (!$this->isConnected()) {
            return null;
        }
        $prefix = $this->config['banlist_db_prefix'] ?? 'amx_';
        $stmt = $this->pdo->prepare("SELECT * FROM {$prefix}bans WHERE bid = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new Ban($row) : null;
    }

    public function setBanStatus(int $banId, int $status, int $editorUserId): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        $prefix = $this->config['banlist_db_prefix'] ?? 'amx_';

        switch ($status) {
            case self::STATUS_ADMIN_CLOSE:
                $stmt = $this->pdo->prepare("UPDATE {$prefix}bans SET expired = 1 WHERE bid = ?");
                return $stmt->execute([$banId]);
            case self::STATUS_USER_BUY_UNBAN:
                $stmt = $this->pdo->prepare("UPDATE {$prefix}bans SET expired = 1, ban_reason = CONCAT(ban_reason, ' [PAID]') WHERE bid = ?");
                return $stmt->execute([$banId]);
            default:
                return false;
        }
    }

    public function setBanEnd(int $banId, int $endTimestamp, int $editorUserId): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        $prefix = $this->config['banlist_db_prefix'] ?? 'amx_';
        $ban = $this->getBanById($banId);
        if (!$ban) {
            return false;
        }

        if ($endTimestamp == 0) {
            $length = 0;
        } else {
            $length = max(0, $endTimestamp - $ban->created);
        }

        $expired = ($length > 0 && $endTimestamp <= time()) ? 1 : 0;

        $stmt = $this->pdo->prepare(
            "UPDATE {$prefix}bans SET ban_length = ?, expired = ? WHERE bid = ?"
        );
        return $stmt->execute([$length, $expired, $banId]);
    }

    public function deleteBans(int $mode): int
    {
        if (!$this->isConnected()) {
            return 0;
        }
        $prefix = $this->config['banlist_db_prefix'] ?? 'amx_';

        if ($mode === self::DELETE_BANS_ALL) {
            return $this->pdo->exec("DELETE FROM {$prefix}bans");
        }

        $now = time();
        return $this->pdo->exec(
            "DELETE FROM {$prefix}bans WHERE (ban_length > 0 AND ban_created + ban_length < {$now}) OR expired = 1"
        );
    }
}
