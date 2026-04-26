<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Repository;

use GreyPanel\Model\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function findByRememberToken(string $token): ?User;
    public function findByLoginWithHash(string $login): ?array;
    public function create(User $user, string $plainPassword, int $referralId = 0): int;
    public function update(User $user): void;
    public function updatePassword(int $userId, string $plainPassword): void;
    public function count(): int;
    public function findAllPaginated(int $page, int $perPage): array;
    public function findBySearchPaginated(string $query, int $page, int $perPage): array;
    public function countSearch(string $query): int;
    public function getReferrals(int $userId): array;
    public function getReferralEarnings(int $userId): int;
    public function addReferralEarnings(int $userId, int $amount): void;
    public function updateReferral(int $userId, int $referralId): void;
    public function findTopDonators(int $limit): array;
    public function getRegistrationsLastDays(int $days): array;
}
