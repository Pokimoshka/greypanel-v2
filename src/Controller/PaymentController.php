<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Repository\PaymentRepositoryInterface;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Merchant\YooMoneyMerchant;

final class PaymentController
{
    public function __construct(
        private YooMoneyMerchant $yooMoney,
        private PaymentRepositoryInterface $paymentRepo,
        private SessionServiceInterface $session
    ) {
    }

    public function index(Request $request): Response
    {
        $html = View::render('payment/index.tpl');
        return new Response($html);
    }

    public function yoomoneyForm(Request $request): Response
    {
        if (!$request->isPost()) {
            return new RedirectResponse('/payment');
        }

        $amount = $request->postInt('amount');
        if ($amount < 1 || $amount > 50000) {
            return new RedirectResponse('/payment?error=invalid_amount');
        }

        $userId = $this->session->getUser()?->getId() ?? 0;
        $payId  = uniqid('pay_');

        $this->paymentRepo->add($userId, 'yoomoney', $amount, $payId, 0);

        $form = $this->yooMoney->generateForm((float) $amount, $payId, 'Пополнение баланса');
        if ($form === null) {
            return new RedirectResponse('/payment?error=merchant_disabled');
        }

        return new Response($form);
    }

    public function yoomoneyNotify(Request $request): Response
    {
        $result = $this->yooMoney->processCallback();
        if ($result !== null) {
            [$status, $userId, $amount, $payId] = $result;
            $this->yooMoney->completePayment($userId, $amount, $payId);
        }

        return new Response('OK');
    }

    public function success(Request $request): Response
    {
        $html = View::render('payment/success.tpl');
        return new Response($html);
    }
}
