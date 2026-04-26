<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface ThemeServiceInterface
{
    public function getThemes(): array;
    public function getThemeInfo(string $theme): array;
    public function setActiveTheme(string $theme): bool;
    public function getActiveTheme(): string;
    public function getTemplatePath(): string;
    public function getPublicPath(): string;
}
