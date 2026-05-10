<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Interface\Repository\OnlineRepositoryInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OnlineController extends AbstractController
{
    public function __construct(
        private OnlineRepositoryInterface $onlineRepo,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function data(Request $request): JsonResponse
    {
        $cache = new FilesystemAdapter('widget_online', 0, ROOT_DIR . '/var/cache');
        $data = $cache->get('online_users', function (ItemInterface $item) {
            $item->expiresAfter(30);
            $users = $this->onlineRepo->findOnlineUsers();
            return ['count' => count($users), 'users' => $users];
        });

        return $this->json($data);
    }
}
