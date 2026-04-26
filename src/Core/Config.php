<?php

declare(strict_types=1);

namespace GreyPanel\Core;

class Config
{
    private array $items = [];

    public function __construct(array $env)
    {
        $this->items = $env;
    }

    public function get(string $key, $default = null)
    {
        return $this->items[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function set(string $key, $value): void
    {
        $this->items[$key] = $value;
    }
}
