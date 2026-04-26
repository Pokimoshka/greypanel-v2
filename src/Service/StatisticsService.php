<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Integration\Statistics\CsStatsMysqlIntegration;
use GreyPanel\Integration\Statistics\RankMeIntegration;
use GreyPanel\Integration\Statistics\LevelsRanksIntegration;
use GreyPanel\Integration\Statistics\StatisticsIntegration;
use GreyPanel\Model\Statistic;
use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;

class StatisticsService
{
    public const ENGINE_CSSTATS = 1;
    public const ENGINE_AES = 2;
    public const ENGINE_BOTH = 3;
    public const ENGINE_RANKME = 4;
    public const ENGINE_LEVELS_RANKS = 5;

    private ?StatisticsIntegration $integration = null;
    private array $config;

    public function __construct(
        private MonitorServerRepositoryInterface $serverRepo,
        private EncryptionServiceInterface $encryption
    ) {}

    private function getIntegration(): ?StatisticsIntegration
    {
        if ($this->integration !== null) return $this->integration;

        $servers = $this->serverRepo->findAll();
        foreach ($servers as $server) {
            $engine = (int)($server['stats_engine'] ?? 0);
            if ($engine === 0) continue;

            // Сохраняем конфигурацию сервера
            $this->config = $server;

            switch ($engine) {
                case self::ENGINE_CSSTATS:
                case self::ENGINE_BOTH:
                    $this->integration = new CsStatsMysqlIntegration($this->serverRepo, $this->encryption);
                    break 2;
                case self::ENGINE_RANKME:
                    $this->integration = new RankMeIntegration($this->serverRepo, $this->encryption);
                    break 2;
                case self::ENGINE_LEVELS_RANKS:
                    $this->integration = new LevelsRanksIntegration($this->serverRepo, $this->encryption);
                    break 2;
                default:
                    break;
            }
        }

        return $this->integration;
    }

    public function isAvailable(): bool
    {
        $integration = $this->getIntegration();
        return $integration !== null && $integration->isConnected();
    }

    /** @return array<int, Statistic> */
    public function getRanking(int $page = 1, int $perPage = 20, int $sortType = 0, ?string $search = null): array
    {
        $integration = $this->getIntegration();
        if (!$integration) return [];
        return $integration->getRanking($page, $perPage, $sortType, $search);
    }

    public function getTotalPlayers(?string $search = null): int
    {
        $integration = $this->getIntegration();
        if (!$integration) return 0;
        return $integration->getTotalPlayers($search);
    }

    public function getPlayerById(int $id): ?Statistic
    {
        $integration = $this->getIntegration();
        if (!$integration) return null;
        return $integration->getPlayerById($id);
    }

    public function getPlayerBySteamId(string $steamId): ?Statistic
    {
        $integration = $this->getIntegration();
        if (!$integration) return null;
        return $integration->getPlayerBySteamId($steamId);
    }

    public function getSortTypes(): array
    {
        $integration = $this->getIntegration();
        if (!$integration) return [];
        return $integration->getSortTypes();
    }
}