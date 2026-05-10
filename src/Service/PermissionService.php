<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Service\PermissionServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(private SessionServiceInterface $session)
    {
    }

    public function hasPermission(string $permission): bool
    {
        $user = $this->session->getUser();
        return $user && $user->hasPermission($permission);
    }

    public function getFlags(): string
    {
        $user = $this->session->getUser();
        return $user?->getGroup()?->getFlags() ?? '';
    }
}
