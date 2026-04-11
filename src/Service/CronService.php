<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Repository\VipUserRepositoryInterface;
use Psr\Log\LoggerInterface;

final class CronService implements CronServiceInterface
{
    private string $lockFile;
    private int $interval;

    public function __construct(
        private VipUserRepositoryInterface $vipUserRepo,
        private MonitorServiceInterface $monitorService,
        private SettingsServiceInterface $settings,
        private SeoServiceInterface $seoService,
        private ?LoggerInterface $logger = null
    ) {
        $this->lockFile = __DIR__ . '/../../var/last_cron_run.txt';
        $this->interval = 300;
    }

    public function isDue(): bool
    {
        $lastRun = $this->getLastRun();
        return (time() - $lastRun) >= $this->interval;
    }

    public function run(): void
    {
        if (!$this->isDue()) {
            return;
        }

        $this->setLastRun(time());

        $this->logger?->info('Cron started');

        $deleted = $this->vipUserRepo->deleteExpired();
        $this->logger?->info('Deleted expired VIPs', ['count' => $deleted]);

        $this->monitorService->updateAllServers();
        $this->logger?->info('Monitor servers updated');

        if ($this->settings->getBool('seo_sitemap_enabled', true)) {
            $this->seoService->saveSitemap();
        }

        $this->logger?->info('Cron finished');
    }

    private function getLastRun(): int
    {
        if (!file_exists($this->lockFile)) {
            return 0;
        }
        return (int)file_get_contents($this->lockFile);
    }

    private function setLastRun(int $timestamp): void
    {
        file_put_contents($this->lockFile, $timestamp);
    }
}