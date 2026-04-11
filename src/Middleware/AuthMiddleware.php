<?php

namespace GreyPanel\Middleware;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\RedirectResponse;

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