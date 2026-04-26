<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;

class OnlineController
{
    public function __construct(
        private OnlineRepositoryInterface $onlineRepo
    ) {
    }

    public function data(Request $request): JsonResponse
    {
        $users = $this->onlineRepo->findOnlineUsers();
        return new JsonResponse([
            'count' => count($users),
            'users' => $users
        ]);
    }
}
