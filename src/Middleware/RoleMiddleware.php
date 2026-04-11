<?php

namespace GreyPanel\Middleware;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\RedirectResponse;

class RoleMiddleware
{
    private int $requiredGroup;

    public function __construct(int $requiredGroup)
    {
        $this->requiredGroup = $requiredGroup;
    }

    public function handle(Request $request, callable $next): Response
    {
        $userGroup = $_SESSION['user_group'] ?? 0;
        if ($userGroup < $this->requiredGroup) {
            return new RedirectResponse('/');
        }
        return $next($request);
    }
}