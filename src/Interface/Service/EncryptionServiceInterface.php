<?php

declare(strict_types=1);

namespace GreyPanel\Interface\Service;

interface EncryptionServiceInterface
{
    public function encrypt(string $plaintext): string;
    public function decrypt(string $encrypted): string;
}
