<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use GreyPanel\Interface\Service\CronServiceInterface;
use GreyPanel\Interface\Service\MonitorServiceInterface;
use GreyPanel\Interface\Service\SeoServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Repository\UserServiceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;

final class CronService implements CronServiceInterface
{
    private string $lastRunFile;
    private int $interval;

    public function __construct(
        private UserServiceRepository $userServiceRepo,
        private MonitorServiceInterface $monitorService,
        private SettingsServiceInterface $settings,
        private SeoServiceInterface $seoService,
        private OnlineRepositoryInterface $onlineRepo,
        private LockFactory $lockFactory,
        private ?LoggerInterface $logger = null
    ) {
        $this->lastRunFile = __DIR__ . '/../../var/last_cron_run.txt';
        $this->interval = 300;
    }

    public function isDue(): bool
    {
        $lastRun = $this->getLastRun();
        return (time() - $lastRun) >= $this->interval;
    }

    public function run(): void
    {
        $lock = $this->lockFactory->createLock('cron-run', 60);

        if (!$lock->acquire()) {
            $this->logger?->warning('Cron already running, skipping');
            return;
        }

        if (!$this->isDue()) {
            $lock->release();
            return;
        }

        $this->setLastRun(time());

        try {
            $this->logger?->info('Cron started');

            $deleted = $this->userServiceRepo->deleteExpired();
            $this->logger?->info('Deleted expired user services', ['count' => $deleted]);

            $this->monitorService->updateAllServers();
            $this->logger?->info('Monitor servers updated');

            if ($this->settings->getBool('seo_sitemap_enabled', true)) {
                $this->seoService->saveSitemap();
            }

            $deletedOnline = $this->onlineRepo->deleteExpired(300);
            $this->logger?->info('Deleted old online entries', ['count' => $deletedOnline]);

            $this->logger?->info('Cron finished');
        } finally {
            $lock->release();
        }
    }

    private function getLastRun(): int
    {
        if (!file_exists($this->lastRunFile)) {
            return 0;
        }
        return (int)file_get_contents($this->lastRunFile);
    }

    private function setLastRun(int $timestamp): void
    {
        file_put_contents($this->lastRunFile, $timestamp);
    }
}
