<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

use GreyPanel\Model\User;

interface SessionServiceInterface
{
    public function start(): void;
    public function isLoggedIn(): bool;
    public function getUser(): ?User;
    public function setUser(User $user): void;
    public function clear(): void;
    public function getCsrfToken(): string;
    public function validateCsrfToken(?string $token): bool;
    public function setFlash(string $key, string $message): void;
    public function getFlash(string $key): ?string;
    public function hasFlash(string $key): bool;
    public function setReferralId(int $id): void;
    public function getReferralId(): ?int;
    public function clearReferralId(): void;
    public function setLocale(string $locale): void;
    public function getLocale(): ?string;
}
