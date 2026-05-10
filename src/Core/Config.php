<?php

declare(strict_types=1);

namespace GreyPanel\Core;

final class Config
{
    /** @var array<string, string> */
    private array $items = [];

    public function __construct(array $env)
    {
        $this->items = $env;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return array_key_exists($key, $this->items) ? (string)$this->items[$key] : $default;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        return array_key_exists($key, $this->items) ? (bool)$this->items[$key] : $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        return array_key_exists($key, $this->items) ? (int)$this->items[$key] : $default;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function set(string $key, string $value): void
    {
        $this->items[$key] = $value;
    }
}
