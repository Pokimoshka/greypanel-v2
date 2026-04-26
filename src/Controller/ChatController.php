<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Interface\Service\ChatServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Service\PermissionService;

final class ChatController
{
    public function __construct(
        private ChatServiceInterface $chatService,
        private SessionServiceInterface $sessionService,
        private PermissionService $permissionService
    ) {
    }

    public function fetchMessages(Request $request): JsonResponse
    {
        $sinceId = (int)($request->get('last') ?? 0);
        $messages = $this->chatService->getMessages($sinceId);
        return new JsonResponse($messages);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        if (!$this->sessionService->isLoggedIn()) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $message = trim($request->post('message') ?? '');
        if (empty($message)) {
            return new JsonResponse(['error' => 'Message is empty'], 400);
        }

        $data = $this->chatService->sendMessage($this->sessionService->getUserId(), $message);
        return new JsonResponse($data);
    }

    public function deleteMessage(Request $request, int $id): JsonResponse
    {
        if (!$this->permissionService->hasPermission('c')) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        $success = $this->chatService->deleteMessage($id);
        return new JsonResponse(['success' => $success]);
    }
}
