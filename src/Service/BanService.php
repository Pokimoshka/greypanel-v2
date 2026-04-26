<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Integration\Ban\AmxBansIntegration;
use GreyPanel\Integration\Ban\BanSystemIntegration;
use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\BanServiceInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Ban;

class BanService implements BanServiceInterface
{
    private BanSystemIntegration $integration;

    public function __construct(
        MonitorServerRepositoryInterface $serverRepo,
        EncryptionServiceInterface $encryption
    ) {
        $this->integration = new AmxBansIntegration($serverRepo, $encryption);
    }

    // Старые публичные методы для совместимости
    public function getBans(int $page, int $perPage, ?string $search = null, ?int $statusFilter = null): array
    {
        return $this->integration->getBans($page, $perPage, $search, $statusFilter);
    }

    public function countBans(?string $search = null, ?int $statusFilter = null): int
    {
        return $this->integration->countBans($search, $statusFilter);
    }

    public function searchBans(string $query): array
    {
        return $this->integration->getBans(1, 100, $query);
    }

    public function getBanById(int $id): ?array
    {
        $ban = $this->integration->getBanById($id);
        if (!$ban) {
            return null;
        }
        return (array)$ban; // старый контроллер ждёт массив
    }

    public function deleteBan(int $id): bool
    {
        return $this->integration->setBanStatus($id, BanSystemIntegration::STATUS_ADMIN_CLOSE, 0);
    }

    public function isActive(): bool
    {
        return $this->integration->isConnected();
    }

    // Новые методы для админки
    public function getBanModelById(int $id): ?Ban
    {
        return $this->integration->getBanById($id);
    }

    public function updateBanStatus(int $banId, int $status, int $editorUserId): bool
    {
        return $this->integration->setBanStatus($banId, $status, $editorUserId);
    }

    public function updateBanEnd(int $banId, int $endTimestamp, int $editorUserId): bool
    {
        return $this->integration->setBanEnd($banId, $endTimestamp, $editorUserId);
    }

    public function deleteBans(string $mode): int
    {
        $intMode = $mode === 'all' ? BanSystemIntegration::DELETE_BANS_ALL : BanSystemIntegration::DELETE_BANS_EXPIRED;
        return $this->integration->deleteBans($intMode);
    }

    public function getPaginatedBans(int $page, int $perPage, ?string $search = null): array
    {
        return $this->integration->getBans($page, $perPage, $search);
    }

    public function getTotalBans(?string $search = null): int
    {
        return $this->integration->countBans($search);
    }
}
