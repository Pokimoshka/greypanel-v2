<?php

declare(strict_types=1);

namespace GreyPanel\Merchant;

use GreyPanel\Interface\Repository\LogRepositoryInterface;
use GreyPanel\Interface\Repository\MoneyLogRepositoryInterface;
use GreyPanel\Interface\Repository\PaymentRepositoryInterface;
use GreyPanel\Interface\Repository\UserRepositoryInterface;
use GreyPanel\Interface\Service\EncryptionServiceInterface;
use GreyPanel\Interface\Service\SettingsServiceInterface;
use Psr\Log\LoggerInterface;

final class YooMoneyMerchant extends BaseMerchant implements MerchantInterface
{
    private string $wallet;
    private string $secret;
    private string $siteName;

    public function __construct(
        private SettingsServiceInterface $settings,
        private EncryptionServiceInterface $encryption,
        private PaymentRepositoryInterface $paymentRepo,
        private UserRepositoryInterface $userRepo,
        private MoneyLogRepositoryInterface $moneyLogRepo,
        private LogRepositoryInterface $logRepo,
        private ?LoggerInterface $logger = null
    ) {
        $this->wallet = $this->settings->get('yoomoney_wallet', '');
        $encryptedSecret = $this->settings->get('yoomoney_secret', '');
        $this->secret = $encryptedSecret ? $this->encryption->decrypt($encryptedSecret) : '';
        $this->siteName = $this->settings->get('site_name', 'GreyPanel');
    }

    public function getSlug(): string
    {
        return 'yoomoney';
    }
    public function getTitle(): string
    {
        return 'ЮMoney';
    }
    public function isEnabled(): bool
    {
        return !empty($this->wallet) && !empty($this->secret);
    }

    public function generateForm(float $amount, string $payId, string $orderDesc): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $sum = number_format($amount, 2, '.', '');
        $params = [
            'receiver'      => $this->wallet,
            'formcomment'   => $orderDesc,
            'short-dest'    => $orderDesc,
            'label'         => $payId,
            'quickpay-form' => 'shop',
            'targets'       => "Пополнение баланса на сайте {$this->siteName}",
            'sum'           => $sum,
            'comment'       => '',
            'need-fio'      => 'false',
            'need-email'    => 'false',
            'need-phone'    => 'false',
            'need-address'  => 'false',
            'paymentType'   => 'PC',
        ];

        return $this->getForm('https://yoomoney.ru/quickpay/confirm.xml', $params);
    }

    public function processCallback(): ?array
    {
        $params = $_POST;
        $sign = $params['sign'] ?? null;
        unset($params['sign']);

        if (empty($sign)) {
            $this->logger?->warning('YooMoney webhook: sign parameter missing');
            return null;
        }

        ksort($params, SORT_STRING);
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . urlencode($value);
        }
        $signString = implode('&', $parts);
        $expectedSign = hash_hmac('sha256', $signString, $this->secret);

        if (!hash_equals($expectedSign, $sign)) {
            $this->logger?->error('YooMoney invalid sign', [
                'string'   => $signString,
                'expected' => $expectedSign,
                'received' => $sign,
            ]);
            return null;
        }

        $amount = (float)($params['withdraw_amount'] ?? $params['amount'] ?? 0);
        $label  = $params['label'] ?? '';

        if (empty($label) || $amount <= 0) {
            $this->logger?->warning('YooMoney webhook: missing label or invalid amount');
            return null;
        }

        $payment = $this->paymentRepo->findByExternalId($label);
        if (!$payment) {
            $this->logger?->error('YooMoney payment not found', ['label' => $label]);
            return null;
        }
        if ($payment['status'] == 1) {
            $this->logger?->info('YooMoney payment already processed');
            return null;
        }

        return ['OK', (int)$payment['user_id'], $amount, $label];
    }

    public function completePayment(int $userId, float $amount, string $payId): bool
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            return false;
        }

        $amountRounded = (int) round($amount);

        $newMoney = $user->getMoney() + $amountRounded;
        $user->setMoney($newMoney);
        $user->setAllMoney($user->getAllMoney() + $amountRounded);
        $this->userRepo->update($user);

        $this->moneyLogRepo->add($userId, $amountRounded, 'Пополнение через ЮMoney', 0);
        $this->paymentRepo->updateStatus($payId, 1);

        $referralId = $user->getReferral();
        if ($referralId > 0) {
            $referralUser = $this->userRepo->findById($referralId);
            if ($referralUser && !$referralUser->isBanned()) {
                $bonus = (int) round($amountRounded * 0.1);
                if ($bonus > 0) {
                    $newReferralMoney = $referralUser->getMoney() + $bonus;
                    $referralUser->setMoney($newReferralMoney);
                    $this->userRepo->update($referralUser);
                    $this->userRepo->addReferralEarnings($referralId, $bonus);
                    $this->moneyLogRepo->add($referralId, $bonus, 'Реферальный бонус от ' . $user->getUsername(), 0);
                }
            }
        }

        $this->logRepo->add($userId, 'payment', "Пополнение на {$amountRounded} руб. через ЮMoney");
        return true;
    }
}
