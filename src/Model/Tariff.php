<?php

declare(strict_types=1);

namespace GreyPanel\Model;

class Tariff
{
    private ?int $id;
    private int $serviceId;
    private int $durationDays;
    private int $price;
    private bool $isActive;
    private int $sortOrder;
    private int $createdAt;
    private int $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->serviceId = (int)($data['service_id'] ?? 0);
        $this->durationDays = (int)($data['duration_days'] ?? 0);
        $this->price = (int)($data['price'] ?? 0);
        $this->isActive = (bool)($data['is_active'] ?? true);
        $this->sortOrder = (int)($data['sort_order'] ?? 0);
        $this->createdAt = $data['created_at'] ?? time();
        $this->updatedAt = $data['updated_at'] ?? time();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getServiceId(): int
    {
        return $this->serviceId;
    }
    public function getDurationDays(): int
    {
        return $this->durationDays;
    }
    public function getPrice(): int
    {
        return $this->price;
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
    public function setServiceId(int $id): self
    {
        $this->serviceId = $id;
        return $this;
    }
    public function setDurationDays(int $days): self
    {
        $this->durationDays = $days;
        return $this;
    }
    public function setPrice(int $price): self
    {
        $this->price = $price;
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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'serviceId' => $this->serviceId,
            'durationDays' => $this->durationDays,
            'price' => $this->price,
            'isActive' => $this->isActive,
            'sortOrder' => $this->sortOrder,
        ];
    }
}
