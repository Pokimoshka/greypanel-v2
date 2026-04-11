<?php
declare(strict_types=1);

namespace GreyPanel\Service;

final class SessionService implements SessionServiceInterface
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name($_ENV['SESSION_NAME'] ?? 'greysession');
            session_start();
        }
        if (!isset($_SESSION['_initiated'])) {
            session_regenerate_id(true);
            $_SESSION['_initiated'] = true;
        }
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUserGroup(): int
    {
        return $_SESSION['user_group'] ?? 0;
    }

    public function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public function setUser($user): void
    {
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_group'] = $user->getGroup();
        $_SESSION['user'] = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'group' => $user->getGroup(),
            'avatar' => $user->getAvatar(),
            'count_theard' => $user->getCountTheard(),
            'count_post' => $user->getCountPost(),
            'count_like' => $user->getCountLike(),
            'money' => $user->getMoney(),
            'all_money' => $user->getAllMoney(),
        ];
    }

    public function clear(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function getCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken(?string $token): bool
    {
        return $token !== null && hash_equals($this->getCsrfToken(), $token);
    }
}