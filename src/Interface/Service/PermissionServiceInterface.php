<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface PermissionServiceInterface
{
    public function hasPermission(string $permission): bool;
    public function getFlags(): string;
}
