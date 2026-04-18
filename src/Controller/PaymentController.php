<?php

namespace GreyPanel\Controller;

use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Core\RedirectResponse;
use GreyPanel\Repository\LogRepositoryInterface;
use Psr\Log\LoggerInterface;
use GreyPanel\Service\SettingsServiceInterface;
use GreyPanel\Repository\PaymentRepositoryInterface;
use GreyPanel\Repository\UserRepositoryInterface;
use GreyPanel\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Service\EncryptionServiceInterface;

class PaymentController
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepo,
        private UserRepositoryInterface $userRepo,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private LogRepositoryInterface $logRepo,
        private LoggerInterface $logger,
        private SettingsServiceInterface $settings,
        private EncryptionServiceInterface $encryption
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
        $encryptedSecret = $this->settings->get('yoomoney_secret');
        $secret = $encryptedSecret ? $this->encryption->decrypt($encryptedSecret) : '';

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
        // 1. Проверка IP (диапазоны ЮMoney)
        $allowedIps = [
            '77.75.153.0/25', '77.75.154.0/25', '77.75.156.0/26', '77.75.156.64/26',
            '77.75.156.128/26', '77.75.156.192/26', '77.75.158.0/26', '77.75.158.64/26',
            '77.75.158.128/26', '77.75.158.192/26', '185.71.76.0/27', '185.71.76.32/27',
            '185.71.76.64/27', '185.71.76.96/27'
        ];
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$this->ipInRange($clientIp, $allowedIps)) {
            $this->logger->warning('YooMoney notify from untrusted IP: ' . $clientIp);
            return new Response('Forbidden', 403);
        }

        $notification_type = $request->post('notification_type');
        $operation_id = $request->post('operation_id');
        $amount = (float)$request->post('amount');
        $currency = $request->post('currency');
        $datetime = $request->post('datetime');
        $sender = $request->post('sender');
        $codepro = $request->post('codepro');
        $label = $request->post('label');
        $sha1_hash = $request->post('sha1_hash');

        $encryptedSecret = $this->settings->get('yoomoney_secret');
        $secret = $encryptedSecret ? $this->encryption->decrypt($encryptedSecret) : '';

        // Проверка подписи
        $hash_string = $notification_type . '&' . $operation_id . '&' . $amount . '&' . $currency . '&' . $datetime . '&' . $sender . '&' . $codepro . '&' . $secret . '&' . $label;
        $hash = sha1($hash_string);

        if ($hash !== $sha1_hash) {
            $this->logger->error('YooMoney invalid signature', ['hash' => $hash, 'received' => $sha1_hash]);
            return new Response('Invalid signature', 400);
        }

        // Проверка через API ЮMoney
        $checkUrl = "https://yoomoney.ru/api/operation-details?operation_id=" . urlencode($operation_id);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $checkUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $secret . ":");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->logger->error('YooMoney API check failed', ['http_code' => $httpCode]);
            return new Response('API check failed', 400);
        }

        $operation = json_decode($response, true);
        if (!$operation || $operation['status'] !== 'success' || $operation['direction'] !== 'in') {
            $this->logger->error('YooMoney operation not valid', ['operation' => $operation]);
            return new Response('Operation not valid', 400);
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

        // Реферальный бонус
        $referralId = $user->getReferral();
        if ($referralId > 0) {
            $referralUser = $this->userRepo->findById($referralId);
            if ($referralUser && !$referralUser->isBanned()) {
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

    private function ipInRange(string $ip, array $ranges): bool
    {
        foreach ($ranges as $range) {
            if ($this->ipInCidr($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
            return true;
        }
        return false;
    }
}