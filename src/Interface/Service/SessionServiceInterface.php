<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface SessionServiceInterface
{
    public function start(): void;
    public function isLoggedIn(): bool;
    public function getUserId(): ?int;
    public function getUserGroup(): int;
    public function getUser(): ?array;
    public function setUser($user): void;
    public function clear(): void;
    public function getCsrfToken(): string;
    public function validateCsrfToken(?string $token): bool;
}
