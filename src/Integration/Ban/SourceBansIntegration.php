<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Ban;

use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Ban;
use PDO;
use PDOException;

class SourceBansIntegration implements BanSystemIntegration
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
            if (
                !empty($server['banlist_db_host'])
                && in_array((int)($server['privilege_storage'] ?? 0), [2, 3])
            ) {
                $this->config = $server;
                break;
            }
        }

        if (empty($this->config)) {
            return;
        }

        $pass = $this->config['banlist_db_pass']
            ? $this->encryption->decrypt($this->config['banlist_db_pass'])
            : '';

        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=utf8mb4",
            $this->config['banlist_db_host'],
            $this->config['banlist_db_name']
        );

        try {
            $this->pdo = new PDO($dsn, $this->config['banlist_db_user'], $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('SourceBans connection failed: ' . $e->getMessage());
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

        $prefix = $this->config['banlist_db_prefix'] ?? 'sb_';
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT b.bid,
                       b.name AS player_nick,
                       a.user AS admin_nick,
                       b.reason AS ban_reason,
                       '' AS cs_ban_reason,
                       b.created AS ban_created,
                       b.length AS ban_length,
                       b.ends,
                       b.type,
                       b.RemovedBy,
                       b.RemoveType,
                       b.RemovedOn
                FROM {$prefix}bans b
                LEFT JOIN {$prefix}admins a ON b.aid = a.aid
                WHERE b.type = 0";

        $conditions = [];
        $params = [];

        if ($statusFilter !== null) {
            switch ($statusFilter) {
                case Ban::STATUS_ACTIVE:
                    $conditions[] = "b.RemovedBy IS NULL AND (b.length = 0 OR b.ends > UNIX_TIMESTAMP())";
                    break;
                case Ban::STATUS_EXPIRED:
                    $conditions[] = "b.RemovedBy IS NULL AND b.length > 0 AND b.ends <= UNIX_TIMESTAMP()";
                    break;
                case Ban::STATUS_UNBANNED:
                    $conditions[] = "b.RemovedBy IS NOT NULL AND b.RemoveType = 'U'";
                    break;
                case Ban::STATUS_BOUGHT_UNBAN:
                    $conditions[] = "b.RemovedBy IS NOT NULL AND b.RemoveType = 'P'";
                    break;
            }
        }

        if ($search) {
            $like = "%{$search}%";
            $conditions[] = "(b.name LIKE ? OR b.ip LIKE ? OR b.reason LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($conditions) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY b.bid DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(function (array $row): Ban {
            $row['server_name'] = ($this->config['ip'] ?? '') . ':' . ($this->config['c_port'] ?? '');
            $row['player_nick'] = $row['player_nick'] ?? 'Unknown';
            $row['admin_nick']   = $row['admin_nick'] ?? '';
            $row['ban_reason']   = $row['ban_reason'] ?? '';
            $row['cs_ban_reason'] = '';
            $row['ban_created']  = (int)($row['ban_created'] ?? 0);
            $row['ban_length']   = (int)($row['ban_length'] ?? 0);
            $row['expired']      = ($row['RemovedBy'] !== null) ? 1 : 0;
            return new Ban($row);
        }, $rows);
    }

    public function countBans(?string $search = null, ?int $statusFilter = null): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        $prefix = $this->config['banlist_db_prefix'] ?? 'sb_';
        $sql = "SELECT COUNT(*) FROM {$prefix}bans b WHERE b.type = 0";
        $conditions = [];
        $params = [];

        if ($statusFilter !== null) {
            switch ($statusFilter) {
                case Ban::STATUS_ACTIVE:
                    $conditions[] = "b.RemovedBy IS NULL AND (b.length = 0 OR b.ends > UNIX_TIMESTAMP())";
                    break;
                case Ban::STATUS_EXPIRED:
                    $conditions[] = "b.RemovedBy IS NULL AND b.length > 0 AND b.ends <= UNIX_TIMESTAMP()";
                    break;
                case Ban::STATUS_UNBANNED:
                    $conditions[] = "b.RemovedBy IS NOT NULL AND b.RemoveType = 'U'";
                    break;
                case Ban::STATUS_BOUGHT_UNBAN:
                    $conditions[] = "b.RemovedBy IS NOT NULL AND b.RemoveType = 'P'";
                    break;
            }
        }

        if ($search) {
            $like = "%{$search}%";
            $conditions[] = "(b.name LIKE ? OR b.ip LIKE ? OR b.reason LIKE ?)";
            $params = array_merge($params, [$like, $like, $like]);
        }

        if ($conditions) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getBanById(int $id): ?Ban
    {
        if (!$this->isConnected()) {
            return null;
        }

        $prefix = $this->config['banlist_db_prefix'] ?? 'sb_';
        $stmt = $this->pdo->prepare(
            "SELECT b.*, a.user AS admin_nick FROM {$prefix}bans b 
             LEFT JOIN {$prefix}admins a ON b.aid = a.aid 
             WHERE b.bid = ? AND b.type = 0"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $row['server_name'] = ($this->config['ip'] ?? '') . ':' . ($this->config['c_port'] ?? '');
        $row['player_nick']  = $row['name'] ?? $row['player_nick'] ?? 'Unknown';
        $row['ban_reason']   = $row['reason'] ?? '';
        $row['cs_ban_reason'] = '';
        $row['ban_created']  = (int)($row['created'] ?? 0);
        $row['ban_length']   = (int)($row['length'] ?? 0);
        $row['expired']      = ($row['RemovedBy'] !== null) ? 1 : 0;

        return new Ban($row);
    }

    public function setBanStatus(int $banId, int $status, int $editorUserId): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $prefix = $this->config['banlist_db_prefix'] ?? 'sb_';

        if ($status === self::STATUS_ADMIN_CLOSE) {
            $stmt = $this->pdo->prepare(
                "UPDATE {$prefix}bans SET RemovedBy = ?, RemoveType = 'U', RemovedOn = UNIX_TIMESTAMP() WHERE bid = ?"
            );
            return $stmt->execute([$editorUserId, $banId]);
        }

        if ($status === self::STATUS_USER_BUY_UNBAN) {
            $stmt = $this->pdo->prepare(
                "UPDATE {$prefix}bans SET RemovedBy = ?, RemoveType = 'P', RemovedOn = UNIX_TIMESTAMP() WHERE bid = ?"
            );
            return $stmt->execute([$editorUserId, $banId]);
        }

        return false;
    }

    public function setBanEnd(int $banId, int $endTimestamp, int $editorUserId): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $prefix = $this->config['banlist_db_prefix'] ?? 'sb_';
        $length = $endTimestamp === 0 ? 0 : max(0, $endTimestamp - time());

        $stmt = $this->pdo->prepare(
            "UPDATE {$prefix}bans SET length = ?, ends = ? WHERE bid = ? AND type = 0"
        );
        return $stmt->execute([$length, $endTimestamp, $banId]);
    }

    public function deleteBans(int $mode): int
    {
        return 0;
    }
}
