<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Statistic;
use PDO;
use PDOException;

class CsStatsMysqlIntegration implements StatisticsIntegration
{
    private ?PDO $pdo = null;
    private array $config;

    private const SORT_KILLS = 0;
    private const SORT_KILLS_DEATHS_DIFF = 1;
    private const SORT_SKILL = 2;
    private const SORT_TIME = 3;

    public function __construct(
        private MonitorServerRepositoryInterface $serverRepo,
        private EncryptionServiceInterface $encryption
    ) {}

    private function initConnection(): void
    {
        if ($this->pdo !== null) return;

        $servers = $this->serverRepo->findAll();
        foreach ($servers as $server) {
            if (!empty($server['csstats_db_host']) && in_array($server['stats_engine'] ?? 0, [1, 3])) {
                $this->config = $server;
                break;
            }
        }

        if (empty($this->config)) return;

        $pass = $this->config['csstats_db_pass'] ? $this->encryption->decrypt($this->config['csstats_db_pass']) : '';
        $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4",
            $this->config['csstats_db_host'], $this->config['csstats_db_name']);
        try {
            $this->pdo = new PDO($dsn, $this->config['csstats_db_user'], $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('CsStats connection failed: ' . $e->getMessage());
        }
    }

    public function isConnected(): bool
    {
        $this->initConnection();
        return $this->pdo !== null;
    }

    public function isValidSortType(int $type): bool
    {
        return in_array($type, [self::SORT_KILLS, self::SORT_KILLS_DEATHS_DIFF, self::SORT_SKILL, self::SORT_TIME]);
    }

    public function getRanking(int $page, int $perPage, int $sortType, ?string $search = null): array
    {
        if (!$this->isConnected()) return [];
        $offset = ($page - 1) * $perPage;

        $order = match ($sortType) {
            self::SORT_KILLS_DEATHS_DIFF => 'frags - deaths DESC',
            self::SORT_SKILL => 'skill DESC',
            self::SORT_TIME => 'gametime DESC',
            default => 'frags DESC',
        };

        $sql = "SELECT id, nick, authid AS steam_id, frags, deaths, headshots, shots, hits,
                       skill, gametime, lasttime, teamkills, damage, defusing, defused,
                       planted, explode, rounds, wint, winct, connects, suicides
                FROM csstats_players";
        $params = [];
        $where = [];

        if ($search) {
            $like = "%{$search}%";
            $where[] = "(nick LIKE ? OR authid LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $position = $offset + 1;
        return array_map(function($row) use (&$position) {
            $row['position'] = $position++;
            return new Statistic($row);
        }, $rows);
    }

    public function getTotalPlayers(?string $search = null): int
    {
        if (!$this->isConnected()) return 0;
        $sql = "SELECT COUNT(*) FROM csstats_players";
        $params = [];

        if ($search) {
            $like = "%{$search}%";
            $sql .= " WHERE nick LIKE ? OR authid LIKE ?";
            $params = [$like, $like];
        }

        return (int)$this->pdo->prepare($sql)->execute($params)->fetchColumn();
    }

    public function getPlayerById(int $id): ?Statistic
    {
        if (!$this->isConnected()) return null;
        $stmt = $this->pdo->prepare(
            "SELECT id, nick, authid AS steam_id, frags, deaths, headshots, shots, hits,
                    skill, gametime, lasttime, teamkills, damage, defusing, defused,
                    planted, explode, rounds, wint, winct, connects, suicides
             FROM csstats_players WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? new Statistic($row) : null;
    }

    public function getPlayerBySteamId(string $steamId): ?Statistic
    {
        if (!$this->isConnected()) return null;
        $stmt = $this->pdo->prepare(
            "SELECT id, nick, authid AS steam_id, frags, deaths, headshots, shots, hits,
                    skill, gametime, lasttime, teamkills, damage, defusing, defused,
                    planted, explode, rounds, wint, winct, connects, suicides
             FROM csstats_players WHERE authid = ?"
        );
        $stmt->execute([$steamId]);
        $row = $stmt->fetch();
        return $row ? new Statistic($row) : null;
    }
}