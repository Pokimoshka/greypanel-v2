<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface CronServiceInterface
{
    public function isDue(): bool;
    public function run(): void;
}