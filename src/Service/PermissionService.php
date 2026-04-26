<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Model\User;
class PermissionService
{
    /**
     * Загружает права пользователя (группу) в сессию
     */
    public function loadUserPermissions(User $user): void
    {
        $group = $user->getGroup(); // объект UserGroup уже загружен в UserRepository
        if ($group) {
            $_SESSION['user_flags'] = $group->getFlags();
        } else {
            $_SESSION['user_flags'] = '';
        }
    }

    /**
     * Проверяет, имеет ли текущий пользователь право
     */
    public function hasPermission(string $permission): bool
    {
        if (isset($_SESSION['user_flags'])) {
            return str_contains($_SESSION['user_flags'], $permission);
        }
        return false;
    }

    /**
     * Сбрасывает кэш прав (при смене группы)
     */
    public function clearCache(): void
    {
        unset($_SESSION['user_flags']);
    }
}
