<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Interface\Service\ChatServiceInterface;
use GreyPanel\Interface\Service\PermissionServiceInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ChatController extends AbstractController
{
    public function __construct(
        private ChatServiceInterface $chatService,
        private SessionServiceInterface $sessionService,
        private PermissionServiceInterface $permissionService,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function fetchMessages(Request $request): JsonResponse
    {
        $sinceId = ($request->getInt('last'));
        $messages = $this->chatService->getMessages($sinceId);
        return $this->json($messages);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        if (!$this->sessionService->isLoggedIn()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $message = trim($request->postString('message'));
        if (empty($message)) {
            return $this->json(['error' => 'Message is empty'], 400);
        }

        $data = $this->chatService->sendMessage($this->sessionService->getUser()?->getId(), $message);
        return $this->json($data);
    }

    public function deleteMessage(Request $request, int $id): JsonResponse
    {
        if (!$this->permissionService->hasPermission('c')) {
            return $this->json(['error' => 'Forbidden'], 403);
        }
        $success = $this->chatService->deleteMessage($id);
        return $this->json(['success' => $success]);
    }
}
