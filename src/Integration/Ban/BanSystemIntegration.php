<?php

declare(strict_types=1);

namespace GreyPanel\Integration\Ban;

use GreyPanel\Model\Ban;

interface BanSystemIntegration
{
    public const STATUS_APPLICATION_APPROVE = 0;
    public const STATUS_APPLICATION_DISAPPROVE = 1;
    public const STATUS_ADMIN_CLOSE = 2;
    public const STATUS_USER_BUY_UNBAN = 3;
    public const DELETE_BANS_ALL = 1;
    public const DELETE_BANS_EXPIRED = 2;

    /**
     * @return array<int, Ban>
     */
    public function getBans(int $page, int $perPage, ?string $search = null, ?int $statusFilter = null): array;
    public function countBans(?string $search = null, ?int $statusFilter = null): int;
    public function getBanById(int $id): ?Ban;
    public function setBanStatus(int $banId, int $status, int $editorUserId): bool;
    public function setBanEnd(int $banId, int $endTimestamp, int $editorUserId): bool;
    public function deleteBans(int $mode): int;
    public function isConnected(): bool;
}
