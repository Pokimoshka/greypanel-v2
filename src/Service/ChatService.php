<?php
declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Repository\ChatRepositoryInterface;
use GreyPanel\Repository\UserRepositoryInterface;

final class ChatService implements ChatServiceInterface
{
    public function __construct(
        private ChatRepositoryInterface $chatRepo,
        private UserRepositoryInterface $userRepo,
        private MarkdownServiceInterface $markdown
    ) {}

    public function getMessages(int $sinceId = 0, int $limit = 50): array
    {
        $messages = $this->chatRepo->findMessages($sinceId, $limit);
        foreach ($messages as &$msg) {
            $msg['text'] = $this->markdown->parse($msg['message']);
            $msg['time'] = $this->formatTime($msg['created_at']);
            unset($msg['message']);
        }
        return $messages;
    }

    public function sendMessage(int $userId, string $message): array
    {
        $id = $this->chatRepo->addMessage($userId, $message);
        $user = $this->userRepo->findById($userId);
        return [
            'id' => $id,
            'user_id' => $userId,
            'username' => $user->getUsername(),
            'avatar' => $user->getAvatar(),
            'text' => $this->markdown->parse($message),
            'time' => 'только что',
        ];
    }

    public function deleteMessage(int $id): bool
    {
        return $this->chatRepo->deleteMessage($id);
    }

    private function formatTime(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 60) return 'только что';
        if ($diff < 3600) return round($diff / 60) . ' мин назад';
        if ($diff < 86400) return round($diff / 3600) . ' ч назад';
        return date('H:i', $timestamp);
    }
}