<?php

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Core\JsonResponse;
use GreyPanel\Repository\LogRepositoryInterface;
use Psr\Log\LoggerInterface;
use GreyPanel\Service\SettingsServiceInterface;
use GreyPanel\Repository\PaymentRepositoryInterface;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Repository\MoneyLogRepositoryInterface;

class PaymentController
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private UserRepositoryInterface $userRepo,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private LogRepositoryInterface $logRepo,
        private LoggerInterface $logger,
        private SettingsServiceInterface $settings
    ) {}

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

        $amount = (int)$request->post('amount');
        if ($amount < 1 || $amount > 50000) {
            return new RedirectResponse('/payment?error=invalid_amount');
        }

        $userId = $_SESSION['user_id'];
        $paymentId = uniqid('pay_');

        $this->paymentRepo->add($userId, 'yoomoney', $amount, $paymentId, 0);

        $wallet = $this->settings->get('yoomoney_wallet');
        $secret = $this->settings->get('yoomoney_secret');

        $sum = number_format($amount, 2, '.', '');

        $signature = md5("$amount:$paymentId:$secret");

        $params = [
            'receiver' => $wallet,
            'formcomment' => 'Пополнение баланса',
            'short-dest' => 'Пополнение счета',
            'label' => $paymentId,
            'quickpay-form' => 'shop',
            'targets' => "Пополнение баланса на сайте {$_ENV['SITE_NAME']}",
            'sum' => $sum,
            'comment' => '',
            'need-fio' => 'false',
            'need-email' => 'false',
            'need-phone' => 'false',
            'need-address' => 'false',
            'paymentType' => 'PC',
        ];

        $html = View::render('payment/yoomoney_form.tpl', [
            'params' => $params,
            'payment_id' => $paymentId,
        ]);
        return new Response($html);
    }

    public function yoomoneyNotify(Request $request): Response
    {
        $notification_type = $request->post('notification_type');
        $operation_id = $request->post('operation_id');
        $amount = (float)$request->post('amount');
        $currency = $request->post('currency');
        $datetime = $request->post('datetime');
        $sender = $request->post('sender');
        $codepro = $request->post('codepro');
        $label = $request->post('label');
        $sha1_hash = $request->post('sha1_hash');

        $secret = $this->settings->get('yoomoney_secret');

        $hash_string = "$notification_type&$operation_id&$amount&$currency&$datetime&$sender&$codepro&$secret&$label";
        $hash = sha1($hash_string);

        if ($hash !== $sha1_hash) {
            $this->logger->error(0, 'payment_error', 'Invalid signature for YooMoney');
            return new Response('Invalid signature', 400);
        }

        $existing = $this->paymentRepo->findByExternalId($label);
        if ($existing && $existing['status'] == 1) {
            return new Response('Payment already processed');
        }

        if (!$existing) {
            return new Response('Payment not found', 404);
        }

        $userId = $existing['user_id'];
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return new Response('User not found', 404);
        }

        $newMoney = $user->getMoney() + (int)$amount;
        $user->setMoney($newMoney);
        $user->setAllMoney($user->getAllMoney() + (int)$amount);
        $this->userRepo->update($user);

        $this->moneyLogRepo->add($userId, (int)$amount, 'Пополнение через ЮMoney', 0);

        $this->paymentRepo->updateStatus($label, 1);

        $referralId = $user->getReferral();
        if ($referralId > 0) {
            $referralUser = $this->userRepo->findById($referralId);
            if ($referralUser) {
                $bonus = (int)($amount * 0.1);
                if ($bonus > 0) {
                    $newReferralMoney = $referralUser->getMoney() + $bonus;
                    $referralUser->setMoney($newReferralMoney);
                    $this->userRepo->update($referralUser);
                    $this->userRepo->addReferralEarnings($referralId, $bonus);
                    $this->moneyLogRepo->add($referralId, $bonus, 'Реферальный бонус от пользователя ' . $user->getUsername(), 0);
                }
            }
        }

        $this->logRepo->add($userId, 'payment', "Пополнение на {$amount} руб. через ЮMoney");
        return new Response('OK');
    }

    public function success(Request $request): Response
    {
        $html = View::render('payment/success.tpl');
        return new Response($html);
    }
}