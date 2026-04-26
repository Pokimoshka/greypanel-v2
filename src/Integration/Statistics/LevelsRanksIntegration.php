<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Statistic;
use PDO;
use PDOException;

class LevelsRanksIntegration implements StatisticsIntegration
{
    private ?PDO $pdo = null;
    private array $config;
    private string $table = 'lvl_base';

    public function __construct(
        private MonitorServerRepositoryInterface $serverRepo,
        private EncryptionServiceInterface $encryption
    ) {}

    private function initConnection(): void
    {
        if ($this->pdo !== null) return;

        $servers = $this->serverRepo->findAll();
        foreach ($servers as $server) {
            if (!empty($server['csstats_db_host']) && in_array($server['stats_engine'] ?? 0, [5])) {
                $this->config = $server;
                $this->table = $server['csstats_table'] ?: 'lvl_base';
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
            error_log('LevelsRanks connection failed: ' . $e->getMessage());
        }
    }

    public function isConnected(): bool
    {
        $this->initConnection();
        return $this->pdo !== null;
    }

    public function isValidSortType(int $type): bool
    {
        return array_key_exists($type, $this->getSortTypes());
    }

    public function getSortTypes(): array
    {
        return [
            0 => 'По очкам (value)',
            1 => 'По убийствам',
            2 => 'По времени игры',
        ];
    }

    public function getRanking(int $page, int $perPage, int $sortType, ?string $search = null): array
    {
        if (!$this->isConnected()) return [];
        $offset = ($page - 1) * $perPage;

        $order = match ($sortType) {
            1 => 'kills DESC',
            2 => 'playtime DESC',
            default => 'value DESC',
        };

        $sql = "SELECT id, name AS nick, steam AS steam_id, kills AS frags, deaths,
                       headshots, shoots AS shots, hits, value AS skill, playtime AS gametime,
                       lastconnect AS lasttime, round_win, round_lose, suicides,
                       plants AS planted, defuses AS defused, connects
                FROM {$this->table}";
        $params = [];
        $where = [];

        if ($search) {
            $like = "%{$search}%";
            $where[] = "(name LIKE ? OR steam LIKE ?)";
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
            $row['teamkills'] = 0;
            $row['damage'] = 0;
            $row['defusing'] = 0;
            $row['explode'] = 0;
            $row['wint'] = $row['round_win'] ?? 0;
            $row['winct'] = $row['round_lose'] ?? 0;
            $row['rounds'] = ($row['round_win'] ?? 0) + ($row['round_lose'] ?? 0);
            $row['connects'] = $row['connects'] ?? 0;
            return new Statistic($row);
        }, $rows);
    }

    public function getTotalPlayers(?string $search = null): int
    {
        if (!$this->isConnected()) return 0;
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        if ($search) {
            $like = "%{$search}%";
            $sql .= " WHERE name LIKE ? OR steam LIKE ?";
            $params = [$like, $like];
        }
        return (int)$this->pdo->prepare($sql)->execute($params)->fetchColumn();
    }

    public function getPlayerById(int $id): ?Statistic
    {
        if (!$this->isConnected()) return null;
        $stmt = $this->pdo->prepare(
            "SELECT id, name AS nick, steam AS steam_id, kills AS frags, deaths,
                    headshots, shoots AS shots, hits, value AS skill, playtime AS gametime,
                    lastconnect AS lasttime, round_win, round_lose, suicides,
                    plants AS planted, defuses AS defused, connects
             FROM {$this->table} WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['teamkills'] = 0;
        $row['damage'] = 0;
        $row['defusing'] = 0;
        $row['explode'] = 0;
        $row['wint'] = $row['round_win'] ?? 0;
        $row['winct'] = $row['round_lose'] ?? 0;
        $row['rounds'] = ($row['round_win'] ?? 0) + ($row['round_lose'] ?? 0);
        $row['connects'] = $row['connects'] ?? 0;
        return new Statistic($row);
    }

    public function getPlayerBySteamId(string $steamId): ?Statistic
    {
        if (!$this->isConnected()) return null;
        $stmt = $this->pdo->prepare(
            "SELECT id, name AS nick, steam AS steam_id, kills AS frags, deaths,
                    headshots, shoots AS shots, hits, value AS skill, playtime AS gametime,
                    lastconnect AS lasttime, round_win, round_lose, suicides,
                    plants AS planted, defuses AS defused, connects
             FROM {$this->table} WHERE steam = ?"
        );
        $stmt->execute([$steamId]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['teamkills'] = 0;
        $row['damage'] = 0;
        $row['defusing'] = 0;
        $row['explode'] = 0;
        $row['wint'] = $row['round_win'] ?? 0;
        $row['winct'] = $row['round_lose'] ?? 0;
        $row['rounds'] = ($row['round_win'] ?? 0) + ($row['round_lose'] ?? 0);
        $row['connects'] = $row['connects'] ?? 0;
        return new Statistic($row);
    }
}