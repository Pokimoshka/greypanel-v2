<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use GreyPanel\Service\CronService;

class CronController
{
    public function __construct(
        private CronService $cronService,
        private SettingsServiceInterface $settings
    ) {
    }

    public function run(Request $request): Response
    {
        // 1. Только POST-запросы
        if (!$request->isPost()) {
            return new Response('Method not allowed', 405);
        }

        // 2. Проверка CSRF-подобного токена (cron_key)
        $expectedKey = $this->settings->get('cron_key');
        $providedKey = $request->post('cron_key') ?? $request->getRequest()->headers->get('X-Cron-Key');

        if (empty($expectedKey) || $providedKey !== $expectedKey) {
            return new Response('Unauthorized', 403);
        }

        // 3. Дополнительная защита: ограничение по IP (опционально, можно задать в .env)
        $allowedIps = array_filter(explode(',', $_ENV['CRON_ALLOWED_IPS'] ?? ''));
        if (!empty($allowedIps)) {
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
            if (!in_array($clientIp, $allowedIps, true)) {
                return new Response('Forbidden', 403);
            }
        }

        // 4. Запуск задач (CronService сам проверяет isDue())
        $this->cronService->run();
        return new Response('OK');
    }
}
