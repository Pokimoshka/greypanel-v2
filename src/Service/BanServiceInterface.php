<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface BanServiceInterface
{
    /**
     * Получить список банов с активного сервера AmxBans
     */
    public function getBans(int $page, int $perPage): array;

    /**
     * Общее количество банов
     */
    public function countBans(): int;

    /**
     * Поиск банов
     */
    public function searchBans(string $query): array;

    /**
     * Получить бан по ID
     */
    public function getBanById(int $id): ?array;

    /**
     * Удалить бан (для платного разбана)
     */
    public function deleteBan(int $id): bool;

    /**
     * Проверить, активно ли подключение к AmxBans
     */
    public function isActive(): bool;
}