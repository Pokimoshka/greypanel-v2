<?php

declare(strict_types=1);

namespace GreyPanel\Service;

use GreyPanel\Interface\Service\EncryptionServiceInterface;

final class EncryptionService implements EncryptionServiceInterface
{
    private string $key;

    public function __construct(string $encryptionKey)
    {
        $bin = hex2bin($encryptionKey);
        if ($bin === false || strlen($bin) !== 32) {
            throw new \InvalidArgumentException('ENCRYPTION_KEY must be 64 hex characters');
        }
        $this->key = $bin;
    }

    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(16);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $ciphertext = substr($data, 32);
        return openssl_decrypt($ciphertext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}
