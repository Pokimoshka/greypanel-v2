<?php
declare(strict_types=1);

namespace GreyPanel\Service;

interface EncryptionServiceInterface
{
    public function encrypt(string $plaintext): string;
    public function decrypt(string $encrypted): string;
}