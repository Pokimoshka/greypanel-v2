<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Statistics;

use GreyPanel\Model\Statistic;
use GreyPanel\Model\Server;

interface StatisticsIntegration
{
    /**
     * @return array<int, Statistic>
     */
    public function getRanking(int $page, int $perPage, int $sortType, ?string $search = null): array;
    public function getTotalPlayers(?string $search = null): int;
    public function getPlayerById(int $id): ?Statistic;
    public function getPlayerBySteamId(string $steamId): ?Statistic;
    public function isConnected(): bool;
    public function isValidSortType(int $type): bool;
    public function getSortTypes(): array;
}