<?php

declare(strict_types=1);

namespace GreyPanel\Model;

class UserService
{
    private ?int $id;
    private int $userId;
    private int $serviceId;
    private int $tariffId;
    private int $expiresAt;
    private int $createdAt;
    private int $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->userId = (int)($data['user_id'] ?? 0);
        $this->serviceId = (int)($data['service_id'] ?? 0);
        $this->tariffId = (int)($data['tariff_id'] ?? 0);
        $this->expiresAt = (int)($data['expires_at'] ?? 0);
        $this->createdAt = $data['created_at'] ?? time();
        $this->updatedAt = $data['updated_at'] ?? time();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function getServiceId(): int
    {
        return $this->serviceId;
    }
    public function getTariffId(): int
    {
        return $this->tariffId;
    }
    public function getExpiresAt(): int
    {
        return $this->expiresAt;
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
    public function setUserId(int $id): self
    {
        $this->userId = $id;
        return $this;
    }
    public function setServiceId(int $id): self
    {
        $this->serviceId = $id;
        return $this;
    }
    public function setTariffId(int $id): self
    {
        $this->tariffId = $id;
        return $this;
    }
    public function setExpiresAt(int $timestamp): self
    {
        $this->expiresAt = $timestamp;
        return $this;
    }
    public function setUpdatedAt(int $timestamp): self
    {
        $this->updatedAt = $timestamp;
        return $this;
    }
}
