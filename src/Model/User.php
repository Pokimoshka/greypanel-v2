<?php

declare(strict_types=1);

namespace GreyPanel\Model;

final class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private int $money;
    private int $allMoney;
    private string $avatar;
    private int $regData;
    private string $regIp;
    private int $referral;
    private bool $banned;
    private ?string $rememberToken;
    private int $createdAt;
    private int $updatedAt;
    private int $countThread = 0;
    private int $countPost = 0;
    private int $countLike = 0;
    private ?UserGroup $group = null;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->money = (int)($data['money'] ?? 0);
        $this->allMoney = (int)($data['all_money'] ?? 0);
        $this->avatar = $data['avatar'] ?? 'public/assets/img/avatar_default.png';
        $this->regData = (int)($data['reg_data'] ?? 0);
        $this->regIp = $data['reg_ip'] ?? '';
        $this->referral = (int)($data['referral'] ?? 0);
        $this->banned = (bool)($data['banned'] ?? false);
        $this->rememberToken = $data['remember_token'] ?? null;
        $this->createdAt = (int)($data['created_at'] ?? 0);
        $this->updatedAt = (int)($data['updated_at'] ?? 0);
        $this->countThread = (int)($data['count_thread'] ?? 0);
        $this->countPost = (int)($data['count_post'] ?? 0);
        $this->countLike = (int)($data['count_like'] ?? 0);
    }

    // Геттеры
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getMoney(): int
    {
        return $this->money;
    }
    public function getAllMoney(): int
    {
        return $this->allMoney;
    }
    public function getAvatar(): string
    {
        return $this->avatar;
    }
    public function getRegData(): int
    {
        return $this->regData;
    }
    public function getRegIp(): string
    {
        return $this->regIp;
    }
    public function getReferral(): int
    {
        return $this->referral;
    }
    public function isBanned(): bool
    {
        return $this->banned;
    }
    public function getRememberToken(): ?string
    {
        return $this->rememberToken;
    }
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }
    public function getCountThread(): int
    {
        return $this->countThread;
    }
    public function getCountPost(): int
    {
        return $this->countPost;
    }
    public function getCountLike(): int
    {
        return $this->countLike;
    }

    public function getGroup(): ?UserGroup
    {
        return $this->group;
    }
    public function getGroupId(): int
    {
        return $this->group ? $this->group->getId() : 0;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->group !== null && $this->group->hasPermission($permission);
    }

    // Сеттеры
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    public function setMoney(int $money): self
    {
        $this->money = $money;
        return $this;
    }
    public function setAllMoney(int $allMoney): self
    {
        $this->allMoney = $allMoney;
        return $this;
    }
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }
    public function setBanned(bool $banned): self
    {
        $this->banned = $banned;
        return $this;
    }
    public function setRememberToken(?string $token): self
    {
        $this->rememberToken = $token;
        return $this;
    }
    public function setUpdatedAt(int $timestamp): self
    {
        $this->updatedAt = $timestamp;
        return $this;
    }
    public function setCountThread(int $count): self
    {
        $this->countThread = $count;
        return $this;
    }
    public function setCountPost(int $count): self
    {
        $this->countPost = $count;
        return $this;
    }
    public function setCountLike(int $count): self
    {
        $this->countLike = $count;
        return $this;
    }

    public function setGroup(?UserGroup $group): self
    {
        $this->group = $group;
        return $this;
    }
}
