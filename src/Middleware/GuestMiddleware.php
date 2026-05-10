<?php

declare(strict_types=1);

namespace GreyPanel\Middleware;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Interface\Service\SessionServiceInterface;

class GuestMiddleware
{
    public function __construct(
        private SessionServiceInterface $session
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($this->session->isLoggedIn()) {
            return new RedirectResponse('/');
        }
        return $next($request);
    }
}
