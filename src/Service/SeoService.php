<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Core\Database;

final class SeoService implements SeoServiceInterface
{
    private Database $db;
    private SettingsService $settings;
    private string $settingsTable;

    public function __construct(Database $db, SettingsService $settings)
    {
        $this->db = $db;
        $this->settings = $settings;
        $this->settingsTable = $db->table('settings');
    }

    public function getRobotsTxt(): string
    {
        return $this->settings->get('robots_txt') ?: "User-agent: *\nAllow: /\n";
    }

    public function saveRobotsTxt(string $content): void
    {
        $this->settings->set('robots_txt', $content);
    }

    public function generateSitemap(): string
    {
        $baseUrl = rtrim($_ENV['SITE_URL'] ?? '', '/');
        $urls = [];

        $urls[] = ['loc' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'];

        $categories = $this->db->fetchAll("SELECT id FROM " . $this->db->table('forum_categories'));
        foreach ($categories as $cat) {
            $urls[] = [
                'loc' => $baseUrl . '/forum/category/' . $cat['id'],
                'priority' => '0.7',
                'changefreq' => 'weekly'
            ];
        }

        $forums = $this->db->fetchAll("SELECT id FROM " . $this->db->table('forum_forums'));
        foreach ($forums as $forum) {
            $urls[] = [
                'loc' => $baseUrl . '/forum/forum/' . $forum['id'],
                'priority' => '0.6',
                'changefreq' => 'daily'
            ];
        }

        $threads = $this->db->fetchAll("SELECT id, last_post_at FROM " . $this->db->table('forum_threads') . " WHERE is_deleted = 0");
        foreach ($threads as $thread) {
            $urls[] = [
                'loc' => $baseUrl . '/forum/thread/' . $thread['id'],
                'lastmod' => date('c', $thread['last_post_at']),
                'priority' => '0.5',
                'changefreq' => 'weekly'
            ];
        }

        if ($this->settings->getBool('lgsl_active', false)) {
            $urls[] = ['loc' => $baseUrl . '/monitor', 'priority' => '0.4', 'changefreq' => 'daily'];
        }

        if ($this->settings->getBool('amxbans_active', false)) {
            $urls[] = ['loc' => $baseUrl . '/bans', 'priority' => '0.3', 'changefreq' => 'daily'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            if (isset($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

    public function saveSitemap(): string
    {
        $xml = $this->generateSitemap();
        $path = __DIR__ . '/../../public/sitemap.xml';
        file_put_contents($path, $xml);
        return $path;
    }

    public function isSitemapEnabled(): bool
    {
        return $this->settings->getBool('seo_sitemap_enabled', true);
    }

    public function setSitemapEnabled(bool $enabled): void
    {
        $this->settings->set('seo_sitemap_enabled', $enabled ? '1' : '0');
    }

    public function getMetaTags(?string $title = null, ?string $description = null): array
    {
        $defaultTitle = $this->settings->get('sitename') ?? 'GreyPanel';
        $defaultDesc = $this->settings->get('seo_default_description') ?? 'Современная панель управления игровыми серверами';

        return [
            'title' => $title ?: $defaultTitle,
            'description' => $description ?: $defaultDesc,
            'keywords' => $this->settings->get('seo_keywords') ?? '',
        ];
    }
}