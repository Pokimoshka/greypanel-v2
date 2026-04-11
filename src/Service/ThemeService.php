<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Core\Database;

final class ThemeService implements ThemeServiceInterface
{
    private Database $db;
    private string $themesPath;
    private string $activeTheme;
    private string $settingsTable;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->themesPath = __DIR__ . '/../../public/themes';
        $this->settingsTable = $db->table('settings');
        $this->activeTheme = $this->getActiveThemeFromDb();
    }

    public function getThemes(): array
    {
        $themes = [];
        foreach (glob($this->themesPath . '/*', GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            if ($name === 'admin') continue;
            $themes[$name] = $this->loadManifest($name);
        }
        return $themes;
    }

    public function getThemeInfo(string $theme): array
    {
        return $this->loadManifest($theme);
    }

    public function setActiveTheme(string $theme): bool
    {
        if (!is_dir($this->themesPath . '/' . $theme)) {
            return false;
        }
        $this->activeTheme = $theme;
        $this->saveActiveThemeToDb($theme);
        return true;
    }

    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }

    public function getTemplatePath(): string
    {
        return $this->themesPath . '/' . $this->activeTheme . '/tpl';
    }

    public function getPublicPath(): string
    {
        return '/themes/' . $this->activeTheme . '/assets';
    }

    private function loadManifest(string $theme): array
    {
        $file = $this->themesPath . '/' . $theme . '/theme.json';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return json_decode($content, true) ?: ['name' => $theme];
        }
        return ['name' => $theme, 'description' => ''];
    }

    private function getActiveThemeFromDb(): string
    {
        $row = $this->db->fetchOne("SELECT `value` FROM {$this->settingsTable} WHERE `key` = 'active_theme'");
        return $row ? $row['value'] : 'default';
    }

    private function saveActiveThemeToDb(string $theme): void
    {
        $this->db->query(
            "INSERT INTO {$this->settingsTable} (`key`, `value`) VALUES ('active_theme', ?) ON DUPLICATE KEY UPDATE `value` = ?",
            [$theme, $theme]
        );
    }
}