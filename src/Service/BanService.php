<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Integration\Ban\AmxBansIntegration;
use GreyPanel\Integration\Ban\BanSystemIntegration;
use GreyPanel\Integration\Ban\SourceBansIntegration;
use GreyPanel\Interface\Repository\MonitorServerRepositoryInterface;
use GreyPanel\Interface\Service\BanServiceInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Model\Ban;

class BanService implements BanServiceInterface
{
    private ?BanSystemIntegration $integration = null;

    public function __construct(
        MonitorServerRepositoryInterface $serverRepo,
        EncryptionServiceInterface $encryption
    ) {
        $servers = $serverRepo->findAll();
        foreach ($servers as $server) {
            $type = (int)($server['privilege_storage'] ?? 0);
            if (in_array($type, [2, 3])) {
                $this->integration = new AmxBansIntegration($serverRepo, $encryption);
                break;
            }
            if ($type === 4) {
                $this->integration = new SourceBansIntegration($serverRepo, $encryption);
                break;
            }
        }
    }

    public function getBans(int $page, int $perPage, ?string $search = null, ?int $statusFilter = null): array
    {
        if ($this->integration === null) {
            return [];
        }

        return $this->integration->getBans($page, $perPage, $search, $statusFilter);
    }

    public function countBans(?string $search = null, ?int $statusFilter = null): int
    {
        if ($this->integration === null) {
            return 0;
        }
        return $this->integration->countBans($search, $statusFilter);
    }

    public function searchBans(string $query): array
    {
        if ($this->integration === null) {
            return [];
        }

        return $this->integration->getBans(1, 100, $query);
    }

    public function getBanById(int $id): ?array
    {
        if ($this->integration === null) {
            return null;
        }
        $ban = $this->integration->getBanById($id);
        return $ban ? (array)$ban : null;
    }

    public function deleteBan(int $id): bool
    {
        if ($this->integration === null) {
            return false;
        }
        return $this->integration->setBanStatus($id, BanSystemIntegration::STATUS_ADMIN_CLOSE, 0);
    }

    public function isActive(): bool
    {
        if ($this->integration === null) {
            return false;
        }
        return $this->integration->isConnected();
    }

    public function getBanModelById(int $id): ?Ban
    {
        if ($this->integration === null) {
            return null;
        }

        return $this->integration->getBanById($id);
    }

    public function updateBanStatus(int $banId, int $status, int $editorUserId): bool
    {
        if ($this->integration === null) {
            return false;
        }

        return $this->integration->setBanStatus($banId, $status, $editorUserId);
    }

    public function updateBanEnd(int $banId, int $endTimestamp, int $editorUserId): bool
    {
        if ($this->integration === null) {
            return false;
        }

        return $this->integration->setBanEnd($banId, $endTimestamp, $editorUserId);
    }

    public function deleteBans(string $mode): int
    {
        if ($this->integration === null) {
            return 0;
        }

        $intMode = $mode === 'all' ? BanSystemIntegration::DELETE_BANS_ALL : BanSystemIntegration::DELETE_BANS_EXPIRED;
        return $this->integration->deleteBans($intMode);
    }

    public function getPaginatedBans(int $page, int $perPage, ?string $search = null): array
    {
        if ($this->integration === null) {
            return [];
        }

        return $this->integration->getBans($page, $perPage, $search);
    }

    public function getTotalBans(?string $search = null): int
    {
        if ($this->integration === null) {
            return 0;
        }

        return $this->integration->countBans($search);
    }
}
