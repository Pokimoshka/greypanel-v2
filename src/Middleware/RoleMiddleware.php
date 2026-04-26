<?php

declare(strict_types=1);

namespace GreyPanel\Middleware;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;

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
