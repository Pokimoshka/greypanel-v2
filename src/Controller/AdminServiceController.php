<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Helper\FlagsHelper;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Model\Service;
use GreyPanel\Model\Tariff;
use GreyPanel\Repository\MonitorServerRepository;
use GreyPanel\Repository\ServiceRepository;
use GreyPanel\Repository\ServiceServerRepository;
use GreyPanel\Repository\TariffRepository;
use GreyPanel\Repository\UserGroupRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminServiceController extends AbstractController
{
    public function __construct(
        private ServiceRepository $serviceRepo,
        private TariffRepository $tariffRepo,
        private ServiceServerRepository $serviceServerRepo,
        private MonitorServerRepository $serverRepo,
        private UserGroupRepository $groupRepo,
        private SessionServiceInterface $session,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($serializer, $validator, $translator);
    }

    public function index(Request $request): Response
    {
        $services = $this->serviceRepo->findAll();
        $data = [];
        foreach ($services as $service) {
            $tariffs = $this->tariffRepo->findByServiceId($service->getId(), false);
            $data[] = [
                'service' => $service->toArray(),
                'tariffs' => array_map(fn ($t) => $t->toArray(), $tariffs),
                'tariffs_count' => count($tariffs),
                'selected_service_id' => $request->get('service_id'),
            ];
        }
        $allServers = $this->serverRepo->findEnabled();
        $allGroups = $this->groupRepo->findAll();
        return new Response(View::render('services/index.tpl', [
            'services' => $data,
            'all_servers' => $allServers,
            'all_groups' => $allGroups,
        ]));
    }

    public function form(Request $request, ?int $id = null): Response
    {
        $service = null;
        $selectedServerIds = [];
        if ($id !== null) {
            $service = $this->serviceRepo->findById($id);
            if (!$service) {
                return new RedirectResponse('/admin/services');
            }
            $selectedServerIds = $this->serviceServerRepo->getServerIdsForService($id);
        }

        if ($request->isPost()) {
            $name = $request->postString('name');
            $description = $request->postString('description');
            $rights = FlagsHelper::normalize($request->postString('rights'));
            $isActive = $request->postBool('is_active');
            $sortOrder = $request->postInt('sort_order', 0);
            $serverIds = array_map('intval', $request->postArray('servers', []));
            $groupId = $request->postInt('group_id') ? $request->postInt('group_id') : null;

            if ($name === '') {
                return new Response(View::render('services/form.tpl', [
                    'service' => $service,
                    'selected_server_ids' => $selectedServerIds,
                    'all_servers' => $this->serverRepo->findEnabled(),
                    'error' => $this->translator->trans('admin.service_name_required'),
                ]));
            }

            if ($id === null) {
                $newService = new Service([
                    'name' => $name,
                    'description' => $description,
                    'rights' => $rights,
                    'is_active' => $isActive,
                    'sort_order' => $sortOrder,
                    'group_id' => $groupId,
                ]);
                $newId = $this->serviceRepo->create($newService);
                $this->serviceServerRepo->setServersForService($newId, $serverIds);
                $msg = $this->translator->trans('admin.service_created');
            } else {
                $service->setName($name);
                $service->setDescription($description);
                $service->setRights($rights);
                $service->setIsActive($isActive);
                $service->setSortOrder($sortOrder);
                $service->setUpdatedAt(time());
                $service->setGroupId($groupId);
                $this->serviceRepo->update($service);
                $this->serviceServerRepo->setServersForService($id, $serverIds);
                $msg = $this->translator->trans('admin.service_updated');
            }

            $this->session->setFlash('success', $msg);
            return new RedirectResponse('/admin/services');
        }

        return new Response(View::render('services/form.tpl', [
            'service' => $service,
            'selected_server_ids' => $selectedServerIds,
            'all_servers' => $this->serverRepo->findEnabled(),
            'all_groups' => $this->groupRepo->findAll(),
        ]));
    }

    public function delete(Request $request, int $id): Response
    {
        $this->serviceRepo->delete($id);
        $this->session->setFlash('success', $this->translator->trans('admin.service_deleted'));
        return new RedirectResponse('/admin/services');
    }

    public function apiUpdateService(int $id, Request $request): JsonResponse
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) {
            return $this->json(['error' => 'Услуга не найдена'], 404);
        }

        $raw = $request->getRequest()->getContent();
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Неверный формат данных'], 400);
        }

        try {
            $service->setName((string)$data['name'] ?? $service->getName());
            $service->setDescription((string)$data['description'] ?? $service->getDescription());
            $rights = isset($data['rights']) ? FlagsHelper::normalize($data['rights']) : $service->getRights();
            $service->setRights($rights);
            $service->setIsActive(isset($data['isActive']) ? (bool)$data['isActive'] : $service->isActive());
            $service->setSortOrder(isset($data['sortOrder']) ? (int)$data['sortOrder'] : $service->getSortOrder());

            $groupId = $data['groupId'] ?? null;
            if ($groupId === '' || $groupId === 0) {
                $groupId = null;
            } else {
                $groupId = (int)$groupId;
            }
            $service->setGroupId($groupId);
            $service->setUpdatedAt(time());
            $this->serviceRepo->update($service);

            if (isset($data['servers']) && is_array($data['servers'])) {
                $serverIds = array_map('intval', $data['servers']);
                $this->serviceServerRepo->setServersForService($id, $serverIds);
            }

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->logger?->error('apiUpdateService error: ' . $e->getMessage());
            return $this->json(['error' => $this->translator->trans('admin.service_update_error')], 500);
        }
    }

    public function apiUpdateTariff(int $serviceId, int $id, Request $request): JsonResponse
    {
        $tariff = $this->tariffRepo->findById($id);
        if (!$tariff || $tariff->getServiceId() !== $serviceId) {
            return $this->json(['error' => 'Тариф не найден'], 404);
        }

        $raw = $request->getRequest()->getContent();
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Неверный формат данных'], 400);
        }

        try {
            $tariff->setDurationDays(isset($data['durationDays']) ? (int)$data['durationDays'] : $tariff->getDurationDays());
            $tariff->setPrice(isset($data['price']) ? (int)$data['price'] : $tariff->getPrice());
            $tariff->setIsActive(isset($data['isActive']) ? (bool)$data['isActive'] : $tariff->isActive());
            $tariff->setSortOrder(isset($data['sortOrder']) ? (int)$data['sortOrder'] : $tariff->getSortOrder());
            $tariff->setUpdatedAt(time());
            $this->tariffRepo->update($tariff);

            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->logger?->error('apiUpdateTariff error: ' . $e->getMessage());
            return $this->json(['error' => $this->translator->trans('admin.tariff_update_error')], 500);
        }
    }

    public function createTariff(Request $request, int $serviceId): RedirectResponse
    {
        $durationDays = $request->postInt('duration_days', 0);
        $price = $request->postInt('price', 0);
        $isActive = $request->postBool('is_active', false);
        $sortOrder = $request->postInt('sort_order', 0);

        if ($durationDays <= 0 || $price <= 0) {
            $this->session->setFlash('error', $this->translator->trans('admin.tariff_invalid'));
            return new RedirectResponse('/admin/services?service_id=' . $serviceId);
        }

        $newTariff = new Tariff([
            'service_id' => $serviceId,
            'duration_days' => $durationDays,
            'price' => $price,
            'is_active' => $isActive,
            'sort_order' => $sortOrder,
        ]);
        $this->tariffRepo->create($newTariff);
        $this->session->setFlash('success', $this->translator->trans('admin.tariff_created'));
        return new RedirectResponse('/admin/services?service_id=' . $serviceId);
    }

    public function deleteTariff(Request $request, int $serviceId, int $id): RedirectResponse
    {
        $this->tariffRepo->delete($id);
        $this->session->setFlash('success', $this->translator->trans('admin.tariff_deleted'));
        return new RedirectResponse('/admin/services');
    }
}
