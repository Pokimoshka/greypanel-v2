<?php

declare(strict_types=1);

namespace GreyPanel\Middleware;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Service\PermissionService;

class PermissionMiddleware
{
    private PermissionService $permissionService;
    private string $requiredPermission;

    public function __construct(PermissionService $permissionService, string $requiredPermission)
    {
        $this->permissionService = $permissionService;
        $this->requiredPermission = $requiredPermission;
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->permissionService->hasPermission($this->requiredPermission)) {
            return new RedirectResponse('/');
        }
        return $next($request);
    }
}
