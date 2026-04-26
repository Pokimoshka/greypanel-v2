<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface SettingsServiceInterface
{
    public function get(string $key, ?string $default = null): ?string;
    public function set(string $key, string $value): void;
    public function getInt(string $key, int $default = 0): int;
    public function getBool(string $key, bool $default = false): bool;
}
