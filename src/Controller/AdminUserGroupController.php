<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Helper\FlagsHelper;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Model\UserGroup;
use GreyPanel\Repository\UserGroupRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminUserGroupController
{
    public function __construct(
        private SessionServiceInterface $session,
        private UserGroupRepository $groupRepo,
        private TranslatorInterface $translator
    ) {
    }

    public function index(Request $request): Response
    {
        $groups = $this->groupRepo->findAll();
        return new Response(View::render('groups/index.tpl', [
            'groups' => $groups,
        ]));
    }

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
            $name = trim($request->postString('name', ''));
            $flags = FlagsHelper::normalize(trim($request->postString('flags', '')));
            $isDefault = $request->postBool('is_default', false);

            if ($name === '') {
                return new Response(View::render('groups/form.tpl', [
                    'group' => $group,
                    'error' => $this->translator->trans('admin.group_name_required'),
                ]));
            }

            if ($id === null) {
                $newGroup = new UserGroup([
                    'name' => $name,
                    'flags' => $flags,
                    'is_default' => $isDefault,
                ]);
                $this->groupRepo->create($newGroup);
                $msg = $this->translator->trans('admin.group_created');
            } else {
                $group->setName($name);
                $group->setFlags($flags);
                $group->setIsDefault($isDefault);
                $group->setUpdatedAt(time());
                $this->groupRepo->update($group);
                $msg = $this->translator->trans('admin.group_updated');
            }

            $this->session->setFlash('success', $msg);
            return new RedirectResponse('/admin/groups');
        }

        return new Response(View::render('groups/form.tpl', [
            'group' => $group,
        ]));
    }

    public function delete(Request $request, int $id): Response
    {
        try {
            $this->groupRepo->delete($id);
            $this->session->setFlash('success', $this->translator->trans('admin.group_deleted'));
        } catch (\RuntimeException $e) {
            $this->session->setFlash('error', $e->getMessage());
        }
        return new RedirectResponse('/admin/groups');
    }
}
