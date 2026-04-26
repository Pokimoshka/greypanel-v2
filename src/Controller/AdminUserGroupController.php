<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Helper\FlagsHelper;
use GreyPanel\Model\UserGroup;
use GreyPanel\Repository\UserGroupRepository;

final class AdminUserGroupController
{
    public function __construct(
        private UserGroupRepository $groupRepo
    ) {
    }

    /**
     * Список групп
     */
    public function index(Request $request): Response
    {
        $groups = $this->groupRepo->findAll();
        return new Response(View::render('groups/index.tpl', [
            'groups' => $groups,
        ]));
    }

    /**
     * Форма создания/редактирования
     */
    public function form(Request $request, ?int $id = null): Response
    {
        $group = null;
        if ($id !== null) {
            $group = $this->groupRepo->findById($id);
            if (!$group) {
                return new RedirectResponse('/admin/groups');
            }
        }

        if ($request->isPost()) {
            $name = trim($request->post('name', ''));
            $flags = FlagsHelper::normalize(trim($request->post('flags', '')));
            $isDefault = (bool)$request->post('is_default', false);

            if ($name === '') {
                return new Response(View::render('groups/form.tpl', [
                    'group' => $group,
                    'error' => 'Название группы обязательно',
                ]));
            }

            if ($id === null) {
                // Создание
                $newGroup = new UserGroup([
                    'name' => $name,
                    'flags' => $flags,
                    'is_default' => $isDefault,
                ]);
                $this->groupRepo->create($newGroup);
            } else {
                // Обновление
                $group->setName($name);
                $group->setFlags($flags);
                $group->setIsDefault($isDefault);
                $group->setUpdatedAt(time());
                $this->groupRepo->update($group);
            }

            return new RedirectResponse('/admin/groups');
        }

        return new Response(View::render('groups/form.tpl', [
            'group' => $group,
        ]));
    }

    /**
     * Удаление группы
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            $this->groupRepo->delete($id);
        } catch (\RuntimeException $e) {
            // Ошибка при удалении (например, есть пользователи)
            $_SESSION['flash_error'] = $e->getMessage();
        }
        return new RedirectResponse('/admin/groups');
    }
}
