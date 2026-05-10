<?php

declare(strict_types=1);

namespace GreyPanel\Merchant;

interface MerchantInterface
{
    public function getSlug(): string;
    public function getTitle(): string;
    public function isEnabled(): bool;
    public function generateForm(float $amount, string $payId, string $orderDesc): ?string;
    public function processCallback(): ?array;
}
