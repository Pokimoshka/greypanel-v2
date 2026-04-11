<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface SeoServiceInterface
{
    public function getRobotsTxt(): string;
    public function saveRobotsTxt(string $content): void;
    public function generateSitemap(): string;
    public function saveSitemap(): string;
    public function isSitemapEnabled(): bool;
    public function setSitemapEnabled(bool $enabled): void;
    public function getMetaTags(?string $title = null, ?string $description = null): array;
}