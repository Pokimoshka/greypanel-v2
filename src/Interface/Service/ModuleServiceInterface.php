<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface ModuleServiceInterface
{
    public function isEnabled(string $name): bool;
    public function setEnabled(string $name, bool $enabled): void;
    public function getAll(): array;
}
