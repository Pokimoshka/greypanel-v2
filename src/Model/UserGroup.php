<?php

declare(strict_types=1);

namespace GreyPanel\Model;

class UserGroup
{
    private ?int $id;
    private string $name;
    private string $flags;
    private bool $isDefault;
    private int $createdAt;
    private int $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->flags = $data['flags'] ?? '';
        $this->isDefault = (bool)($data['is_default'] ?? false);
        $this->createdAt = $data['created_at'] ?? time();
        $this->updatedAt = $data['updated_at'] ?? time();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getFlags(): string
    {
        return $this->flags;
    }
    public function isDefault(): bool
    {
        return $this->isDefault;
    }
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    // Setters
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    public function setFlags(string $flags): self
    {
        $this->flags = $flags;
        return $this;
    }
    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }
    public function setUpdatedAt(int $timestamp): self
    {
        $this->updatedAt = $timestamp;
        return $this;
    }

    // Проверка наличия права
    public function hasPermission(string $permission): bool
    {
        return str_contains($this->flags, $permission);
    }
}
