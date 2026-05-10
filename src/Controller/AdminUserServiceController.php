<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Repository\MonitorServerRepository;
use GreyPanel\Repository\ServiceRepository;
use GreyPanel\Repository\ServiceServerRepository;
use GreyPanel\Repository\TariffRepository;
use GreyPanel\Repository\UserGroupRepository;
use GreyPanel\Repository\UserRepository;
use GreyPanel\Repository\UserServiceRepository;
use GreyPanel\Service\ServiceActivationService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminUserServiceController
{
    public function __construct(
        private UserServiceRepository $userServiceRepo,
        private ServiceRepository $serviceRepo,
        private TariffRepository $tariffRepo,
        private UserRepository $userRepo,
        private ServiceActivationService $activationService,
        private SessionServiceInterface $session,
        private ServiceServerRepository $serviceServerRepo,
        private MonitorServerRepository $serverRepo,
        private UserGroupRepository $groupRepo,
        private TranslatorInterface $translator,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function listForUser(Request $request, int $userId): Response
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return new RedirectResponse('/admin/users');
        }

        $userServices = $this->userServiceRepo->findByUser($userId, false);
        $servicesData = [];
        foreach ($userServices as $us) {
            $service = $this->serviceRepo->findById($us->getServiceId());
            $tariff = $this->tariffRepo->findById($us->getTariffId());
            $isActive = ($us->getExpiresAt() === 0 || $us->getExpiresAt() > time());
            $servicesData[] = [
                'id' => $us->getId(),
                'service_name' => $service?->getName() ?? 'Удалённая услуга',
                'tariff_days' => $tariff?->getDurationDays() ?? 0,
                'expires_at' => $us->getExpiresAt(),
                'created_at' => $us->getCreatedAt(),
                'is_active' => $isActive,
                'flags' => $service?->getRights() ?? '',
                'server_ids' => $this->serviceServerRepo->getServerIdsForService($us->getServiceId()),
                'service' => $service,
                'tariff' => $tariff,
            ];
        }

        return new Response(View::render('user_services/index.tpl', [
            'user' => $user,
            'services' => $servicesData,
        ]));
    }

    public function addForm(Request $request, int $userId): Response
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return new RedirectResponse('/admin/users');
        }

        if ($request->isPost()) {
            $serviceId = $request->postInt('service_id');
            $tariffId = $request->postInt('tariff_id');
            $password = trim($request->postString('password'));

            $service = $this->serviceRepo->findById($serviceId);
            $tariff = $this->tariffRepo->findById($tariffId);

            if (!$service || !$tariff) {
                $this->session->setFlash('error', $this->translator->trans('admin.service_or_tariff_not_found'));
                return new RedirectResponse("/admin/users/{$userId}/services/add");
            }

            if (empty($password)) {
                $this->session->setFlash('error', $this->translator->trans('admin.password_required'));
                return new RedirectResponse("/admin/users/{$userId}/services/add");
            }

            $success = $this->activationService->activate($user, $service, $tariff, $password);
            if ($success) {
                $this->session->setFlash('success', $this->translator->trans('admin.service_issued'));
            } else {
                $this->session->setFlash('error', $this->translator->trans('admin.service_activation_failed'));
            }
            return new RedirectResponse("/admin/users/{$userId}/services");
        }

        $allServices = $this->serviceRepo->findActive();
        $allServers = $this->serverRepo->findEnabled();
        $allGroups = $this->groupRepo->findAll();

        return new Response(View::render('admin/user_services/add.tpl', [
            'user' => $user,
            'all_services' => $allServices,
            'all_servers' => $allServers,
            'all_groups' => $allGroups,
        ]));
    }

    public function delete(Request $request, int $userId, int $userServiceId): Response
    {
        $userService = $this->userServiceRepo->findById($userServiceId);
        if (!$userService || $userService->getUserId() !== $userId) {
            return new RedirectResponse("/admin/users/{$userId}/services");
        }

        $this->userServiceRepo->delete($userServiceId);
        $this->session->setFlash('success', $this->translator->trans('admin.user_service_deleted'));

        return new RedirectResponse("/admin/users/{$userId}/services");
    }

    public function editForm(Request $request, int $userId, int $userServiceId): Response
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return new RedirectResponse("/admin/users/{$userId}/services");
        }

        $userService = $this->userServiceRepo->findById($userServiceId);
        if (!$userService || $userService->getUserId() !== $userId) {
            return new RedirectResponse("/admin/users/{$userId}/services");
        }

        $service = $this->serviceRepo->findById($userService->getServiceId());
        $tariff = $this->tariffRepo->findById($userService->getTariffId());

        if ($request->isPost()) {
            $newExpires = $request->postString('expires_at');
            if ($newExpires) {
                $timestamp = strtotime($newExpires);
                if ($timestamp !== false) {
                    $userService->setExpiresAt($timestamp);
                    $this->userServiceRepo->update($userService);
                    $this->session->setFlash('success', $this->translator->trans('admin.service_expiry_updated'));
                } else {
                    $this->session->setFlash('success', $this->translator->trans('admin.service_made_permanent'));
                }
            } else {
                $userService->setExpiresAt(0);
                $this->userServiceRepo->update($userService);
                $this->session->setFlash('success', 'Услуга сделана бессрочной.');
            }

            return new RedirectResponse("/admin/users/{$userId}/services");
        }

        return new Response(View::render('admin/user_services/edit.tpl', [
            'user'        => $user,
            'userService' => $userService,
            'service'     => $service,
            'tariff'      => $tariff,
        ]));
    }
}
