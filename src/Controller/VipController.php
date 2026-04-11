<?php
declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Repository\VipServerRepositoryInterface;
use GreyPanel\Repository\VipPrivilegeRepositoryInterface;
use GreyPanel\Repository\VipUserRepositoryInterface;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Repository\LogRepositoryInterface;
use GreyPanel\Service\VipActivationServiceInterface;
use GreyPanel\Service\SessionServiceInterface;

final class VipController
{
    public function __construct(
        private VipServerRepositoryInterface $serverRepo,
        private VipPrivilegeRepositoryInterface $privilegeRepo,
        private VipUserRepositoryInterface $vipUserRepo,
        private UserRepositoryInterface $userRepo,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private LogRepositoryInterface $logRepo,
        private VipActivationServiceInterface $activationService,
        private SessionServiceInterface $session
    ) {}

    public function index(Request $request): Response
    {
        $servers = $this->serverRepo->findAll();
        return new Response(View::render('vip/index.tpl', ['servers' => $servers]));
    }

    public function privileges(Request $request, int $serverId): Response
    {
        $server = $this->serverRepo->findById($serverId);
        if (!$server) {
            return new RedirectResponse('/vip');
        }

        $privileges = $this->privilegeRepo->findByServerId($serverId);
        $userActive = $this->vipUserRepo->findActiveByUserAndServer($this->session->getUserId(), $serverId);

        return new Response(View::render('vip/privileges.tpl', [
            'server' => $server,
            'privileges' => $privileges,
            'user_active' => $userActive,
        ]));
    }

    public function confirm(Request $request): Response
    {
        $serverId = (int)$request->post('server_id');
        $privilegeId = (int)$request->post('privilege_id');
        $days = (int)$request->post('days');

        $server = $this->serverRepo->findById($serverId);
        $privilege = $this->privilegeRepo->findById($privilegeId);

        if (!$server || !$privilege) {
            return new RedirectResponse('/vip');
        }

        $totalPrice = $privilege['price_per_day'] * $days;

        return new Response(View::render('vip/confirm.tpl', [
            'server' => $server,
            'privilege' => $privilege,
            'days' => $days,
            'total_price' => $totalPrice,
        ]));
    }

    public function activate(Request $request): Response
    {
        $serverId = (int)$request->post('server_id');
        $privilegeId = (int)$request->post('privilege_id');
        $days = (int)$request->post('days');
        $userPassword = $request->post('password');

        $server = $this->serverRepo->findById($serverId);
        $privilege = $this->privilegeRepo->findById($privilegeId);
        if (!$server || !$privilege) {
            $_SESSION['flash_error'] = 'Неверные параметры';
            return new RedirectResponse('/vip');
        }

        $totalPrice = $privilege['price_per_day'] * $days;
        $userMoney = $_SESSION['user']['money'] ?? 0;

        if ($userMoney < $totalPrice) {
            $_SESSION['flash_error'] = 'Недостаточно средств';
            return new RedirectResponse('/vip/' . $serverId);
        }

        $success = $this->activationService->activate(
            $this->session->getUserId(),
            $this->session->getUser()['username'],
            $userPassword,
            $serverId,
            $privilegeId,
            $days
        );

        if (!$success) {
            $_SESSION['flash_error'] = 'Ошибка активации привилегии на сервере. Сообщите администратору.';
            return new RedirectResponse('/vip/' . $serverId);
        }

        $newMoney = $userMoney - $totalPrice;
        $user = $this->userRepo->findById($this->session->getUserId());
        $user->setMoney($newMoney);
        $this->userRepo->update($user);
        $_SESSION['user']['money'] = $newMoney;

        $this->moneyLogRepo->add($this->session->getUserId(), $totalPrice, 'Активация привилегии "' . $privilege['title'] . '" на ' . $days . ' дней', 1);
        $this->logRepo->add($this->session->getUserId(), 'vip_activate', "Привилегия {$privilege['title']} на сервере {$server['server_name']} на {$days} дн.");

        $_SESSION['flash_success'] = 'Привилегия успешно активирована!';
        return new RedirectResponse('/vip/success');
    }

    public function success(Request $request): Response
    {
        return new Response(View::render('vip/success.tpl'));
    }
}