<?php

declare(strict_types=1);

return [
    'auth'       => \GreyPanel\Middleware\AuthMiddleware::class,
    'guest'      => \GreyPanel\Middleware\GuestMiddleware::class,
    'csrf'       => \GreyPanel\Middleware\CsrfMiddleware::class,
    'permission' => \GreyPanel\Middleware\PermissionMiddleware::class,
    'rate_limit' => \GreyPanel\Middleware\RateLimitMiddleware::class,
    'locale' => \GreyPanel\Middleware\LocaleMiddleware::class,
];
