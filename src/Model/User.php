<?php

namespace GreyPanel\Model;

class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private int $group;
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
    private int $countTheard = 0;
    private int $countPost = 0;
    private int $countLike = 0;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->group = $data['group'] ?? 0;
        $this->money = $data['money'] ?? 0;
        $this->allMoney = $data['all_money'] ?? 0;
        $this->avatar = $data['avatar'] ?? 'public/assets/img/avatar_default.png';
        $this->regData = $data['reg_data'] ?? 0;
        $this->regIp = $data['reg_ip'] ?? '';
        $this->referral = $data['referral'] ?? 0;
        $this->banned = (bool)($data['banned'] ?? false);
        $this->rememberToken = $data['remember_token'] ?? null;
        $this->createdAt = $data['created_at'] ?? 0;
        $this->updatedAt = $data['updated_at'] ?? 0;
        $this->countTheard = $data['count_theard'] ?? 0;
        $this->countPost = $data['count_post'] ?? 0;
        $this->countLike = $data['count_like'] ?? 0;
    }

    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getGroup(): int { return $this->group; }
    public function getMoney(): int { return $this->money; }
    public function getAllMoney(): int { return $this->allMoney; }
    public function getAvatar(): string { return $this->avatar; }
    public function getRegData(): int { return $this->regData; }
    public function getRegIp(): string { return $this->regIp; }
    public function getReferral(): int { return $this->referral; }
    public function isBanned(): bool { return $this->banned; }
    public function getRememberToken(): ?string { return $this->rememberToken; }
    public function getCreatedAt(): int { return $this->createdAt; }
    public function getUpdatedAt(): int { return $this->updatedAt; }
    public function getCountTheard(): int { return $this->countTheard; }
    public function getCountPost(): int { return $this->countPost; }
    public function getCountLike(): int { return $this->countLike; }

    public function isAdmin(): bool { return $this->group >= 3; }
    public function isRootAdmin(): bool { return $this->group === 4; }
    public function isModerator(): bool { return $this->group >= 2; }

    public function setUsername(string $username): self { $this->username = $username; return $this; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setGroup(int $group): self { $this->group = $group; return $this; }
    public function setMoney(int $money): self { $this->money = $money; return $this; }
    public function setAllMoney(int $allMoney): self { $this->allMoney = $allMoney; return $this; }
    public function setAvatar(string $avatar): self { $this->avatar = $avatar; return $this; }
    public function setBanned(bool $banned): self { $this->banned = $banned; return $this; }
    public function setRememberToken(?string $token): self { $this->rememberToken = $token; return $this; }
    public function setUpdatedAt(int $timestamp): self { $this->updatedAt = $timestamp; return $this; }
    public function setCountTheard(int $count): self { $this->countTheard = $count; return $this; }
    public function setCountPost(int $count): self { $this->countPost = $count; return $this; }
    public function setCountLike(int $count): self { $this->countLike = $count; return $this; }
}