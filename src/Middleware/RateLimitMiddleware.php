<?php

namespace GreyPanel\Middleware;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RateLimitMiddleware
{
    private RateLimiterFactory $factory;
    private string $limitKey;

    public function __construct(string $limitKey, array $config)
    {
        $storage = new CacheStorage(new FilesystemAdapter('rate_limiter', 3600, __DIR__ . '/../../var/cache/rate'));
        $this->factory = new RateLimiterFactory($config, $storage);
        $this->limitKey = $limitKey;
    }

    public function handle(Request $request, callable $next): Response
    {
        $identifier = $this->getIdentifier($request);
        $limiter = $this->factory->create($identifier);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            // Если $retryAfter <= 0, показываем примерное время из конфига
            if ($retryAfter <= 0) {
                $retryAfter = 60; // или взять из конфига
            }
            $minutes = ceil($retryAfter / 60);
            $seconds = $retryAfter % 60;
            if ($minutes > 0) {
                $message = "Слишком много запросов. Попробуйте через {$minutes} мин. {$seconds} сек.";
            } else {
                $message = "Слишком много запросов. Попробуйте через {$seconds} сек.";
            }

            if ($request->isAjax()) {
                return new \GreyPanel\Core\JsonResponse(['error' => $message, 'retry_after' => $retryAfter], 429);
            }

            // Для обычных запросов рендерим шаблон ошибки
            $html = View::render('errors/429.tpl', [
                'message' => $message,
                'retry_after' => $retryAfter,
            ]);
            return new Response($html, 429, ['Retry-After' => $retryAfter]);
        }

        return $next($request);
    }

    private function getIdentifier(Request $request): string
    {
        if ($this->limitKey === 'login' || $this->limitKey === 'register') {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $username = $request->post('username') ?: $request->post('email') ?: '';
            return $this->limitKey . ':' . $ip . ':' . md5($username);
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return $this->limitKey . ':' . ($userId ?: $ip);
    }
}