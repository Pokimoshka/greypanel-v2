<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

use GreyPanel\Model\User;

interface AuthServiceInterface
{
    public function register(string $username, string $email, string $password, string $passwordConfirm, string $ip, int $referralId = 0): User;
    public function login(string $login, string $password): User;
    public function getUserById(int $id): ?User;
    public function setRememberToken(User $user): string;
    public function getUserByRememberToken(string $token): ?User;
    public function clearRememberToken(User $user): void;
}
