<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Model\User;

final class SessionService implements SessionServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private ?SettingsServiceInterface $settings = null
    ) {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionName    = $this->settings?->get('session_name')    ?? 'greysession';
            $sessionLifetime = $this->settings?->getInt('session_lifetime') ?? 7200;
            $secure         = ($this->settings?->get('APP_ENV') === 'prod');

            session_name($sessionName);
            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
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

    public function getUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        $user = $this->userRepo->findById((int)$_SESSION['user_id']);
        if (!$user) {
            $this->clear();
            return null;
        }
        return $user;
    }

    public function setUser(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();
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

    public function setFlash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    public function getFlash(string $key): ?string
    {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public function setReferralId(int $id): void
    {
        $_SESSION['referral'] = $id;
    }

    public function getReferralId(): ?int
    {
        return $_SESSION['referral'] ?? null;
    }

    public function clearReferralId(): void
    {
        unset($_SESSION['referral']);
    }

    public function setLocale(string $locale): void
    {
        $_SESSION['locale'] = $locale;
    }

    public function getLocale(): ?string
    {
        return $_SESSION['locale'] ?? null;
    }
}
