<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\MoneyLogRepositoryInterface;

class BalanceController
{
    public function __construct(private MoneyLogRepositoryInterface $moneyLogRepo)
    {
    }

    public function index(Request $request): Response
    {
        $userId = $_SESSION['user_id'];
        $logs = $this->moneyLogRepo->findByUserId($userId, 20);
        $totalRecharge = $this->moneyLogRepo->getTotalRecharge($userId);

        $html = View::render('balance/index.tpl', [
            'logs' => $logs,
            'total_recharge' => $totalRecharge,
        ]);
        return new Response($html);
    }

    public function history(Request $request): Response
    {
        $userId = $_SESSION['user_id'];
        $page = (int)$request->get('page', 1);
        $perPage = 20;
        $logs = $this->moneyLogRepo->findPaginatedByUserId($userId, $page, $perPage);
        $total = $this->moneyLogRepo->countByUserId($userId);

        $html = View::render('balance/history.tpl', [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
        ]);
        return new Response($html);
    }
}
