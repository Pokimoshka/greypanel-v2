<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface CronServiceInterface
{
    public function isDue(): bool;
    public function run(): void;
}
