<?php

declare(strict_types=1);

namespace GreyPanel\Model;

class Service
{
    private ?int $id;
    private string $name;
    private ?string $description;
    private string $rights;
    private bool $isActive;
    private int $sortOrder;
    private int $createdAt;
    private int $updatedAt;
    private ?int $groupId;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? null;
        $this->rights = $data['rights'] ?? '';
        $this->isActive = (bool)($data['is_active'] ?? true);
        $this->sortOrder = (int)($data['sort_order'] ?? 0);
        $this->createdAt = $data['created_at'] ?? time();
        $this->updatedAt = $data['updated_at'] ?? time();
        $this->groupId = isset($data['group_id']) ? (int)$data['group_id'] : null;
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
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function getRights(): string
    {
        return $this->rights;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function getSortOrder(): int
    {
        return $this->sortOrder;
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
    public function setDescription(?string $desc): self
    {
        $this->description = $desc;
        return $this;
    }
    public function setRights(string $rights): self
    {
        $this->rights = $rights;
        return $this;
    }
    public function setIsActive(bool $active): self
    {
        $this->isActive = $active;
        return $this;
    }
    public function setSortOrder(int $order): self
    {
        $this->sortOrder = $order;
        return $this;
    }
    public function setUpdatedAt(int $timestamp): self
    {
        $this->updatedAt = $timestamp;
        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(?int $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'rights' => $this->rights,
            'isActive' => $this->isActive,
            'sortOrder' => $this->sortOrder,
            'groupId' => $this->groupId,
        ];
    }
}
