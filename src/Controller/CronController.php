<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Service\CronService;
use GreyPanel\Service\SettingsService;

class CronController
{
    public function __construct(
        private CronService $cronService,
        private SettingsService $settings
    ) {}

    public function run(string $key): Response
    {
        $expectedKey = $this->settings->get('cron_key');
        if ($key !== $expectedKey) {
            return new Response('Unauthorized', 403);
        }
        $this->cronService->run();
        return new Response('OK');
    }
}