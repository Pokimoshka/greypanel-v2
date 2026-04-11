<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface VipActivationServiceInterface
{
    public function activate(
        int $userId,
        string $username,
        string $plainPassword,
        int $serverId,
        int $privilegeId,
        int $days
    ): bool;
}