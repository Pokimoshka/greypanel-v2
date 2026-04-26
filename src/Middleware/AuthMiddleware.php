<?php

declare(strict_types=1);

namespace GreyPanel\Middleware;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return new RedirectResponse('/login');
        }
        return $next($request);
    }
}
