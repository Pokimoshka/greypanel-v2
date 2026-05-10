<?php

declare(strict_types=1);

namespace GreyPanel\Middleware;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SessionServiceInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

class RateLimitMiddleware
{
    private RateLimiterFactory $factory;
    private string $limitKey;
    private SessionServiceInterface $session;

    public function __construct(string $limitKey, array $config, SessionServiceInterface $session)
    {
        $storage = new CacheStorage(new FilesystemAdapter('rate_limiter', 3600, __DIR__ . '/../../var/cache/rate'));
        $this->factory = new RateLimiterFactory($config, $storage);
        $this->limitKey = $limitKey;
        $this->session = $session;
    }

    public function handle(Request $request, callable $next): Response
    {
        $identifier = $this->getIdentifier($request);
        $limiter = $this->factory->create($identifier);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            if ($retryAfter <= 0) {
                $retryAfter = 60;
            }
            $minutes = ceil($retryAfter / 60);
            $seconds = $retryAfter % 60;
            if ($minutes > 0) {
                $message = "Слишком много запросов. Попробуйте через {$minutes} мин. {$seconds} сек.";
            } else {
                $message = "Слишком много запросов. Попробуйте через {$seconds} сек.";
            }

            if ($request->isAjax()) {
                return new JsonResponse(['error' => $message, 'retry_after' => $retryAfter], 429);
            }

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
            $ip = $request->getClientIp() ?? '0.0.0.0';
            $username = $request->postString('username') ?: $request->postString('email') ?: '';
            return $this->limitKey . ':' . $ip . ':' . md5($username);
        }
        $userId = $this->session->getUser()?->getId() ?? 0;
        $ip = $request->getClientIp() ?? '0.0.0.0';
        return $this->limitKey . ':' . ($userId ?: $ip);
    }
}
