<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Statistic;
use PDO;
use PDOException;

abstract class AbstractStatsIntegration implements StatisticsIntegration
{
    protected ?PDO $pdo = null;
    protected array $config = [];
    protected string $table = '';

    abstract protected function getEngineType(): int;
    abstract protected function getDefaultTable(): string;
    abstract protected function getBaseQuery(): string;
    abstract protected function mapRow(array $row, int $position): Statistic;
    abstract public function getSortTypes(): array;
    abstract protected function getOrderClause(int $sortType): string;

    public function __construct(
        protected MonitorServerRepositoryInterface $serverRepo,
        protected EncryptionServiceInterface $encryption
    ) {
    }

    protected function initConnection(): void
    {
        if ($this->pdo !== null) {
            return;
        }
        $servers = $this->serverRepo->findAll();
        foreach ($servers as $server) {
            if (!empty($server['csstats_db_host']) && in_array((int)($server['stats_engine'] ?? 0), [$this->getEngineType()])) {
                $this->config = $server;
                $this->table = $server['csstats_table'] ?? $this->getDefaultTable();
                break;
            }
        }
        if (empty($this->config)) {
            return;
        }
        $pass = $this->config['csstats_db_pass'] ? $this->encryption->decrypt($this->config['csstats_db_pass']) : '';
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=utf8mb4",
            $this->config['csstats_db_host'],
            $this->config['csstats_db_name']
        );
        try {
            $this->pdo = new PDO($dsn, $this->config['csstats_db_user'], $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log(get_class($this) . ' connection failed: ' . $e->getMessage());
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

    public function getRanking(int $page, int $perPage, int $sortType, ?string $search = null): array
    {
        if (!$this->isConnected()) {
            return [];
        }
        $offset = ($page - 1) * $perPage;
        $sql = $this->getBaseQuery();
        $params = [];
        $where = [];

        if ($search) {
            $like = "%{$search}%";
            $where[] = "(nick LIKE ? OR steam_id LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY " . $this->getOrderClause($sortType) . " LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $position = $offset + 1;
        return array_map(function ($row) use (&$position) {
            return $this->mapRow($row, $position++);
        }, $rows);
    }

    public function getTotalPlayers(?string $search = null): int
    {
        if (!$this->isConnected()) {
            return 0;
        }
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        if ($search) {
            $like = "%{$search}%";
            $sql .= " WHERE nick LIKE ? OR steam_id LIKE ?";
            $params = [$like, $like];
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPlayerById(int $id): ?Statistic
    {
        if (!$this->isConnected()) {
            return null;
        }
        $sql = $this->getBaseQuery() . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row, 0) : null;
    }

    public function getPlayerBySteamId(string $steamId): ?Statistic
    {
        if (!$this->isConnected()) {
            return null;
        }
        $sql = $this->getBaseQuery() . " WHERE steam_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$steamId]);
        $row = $stmt->fetch();
        return $row ? $this->mapRow($row, 0) : null;
    }
}
