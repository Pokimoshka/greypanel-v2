<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface MonitorServiceInterface
{
    public function getServers(): array;
    public function updateServerStatus(int $id): void;
    public function updateAllServers(): void;
}