<?php

declare(strict_types=1);

namespace GreyPanel\Dto;

use GreyPanel\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterDto
{
    #[Assert\NotBlank(message: 'auth.empty_fields')]
    #[Assert\Length(min: 3, max: 32, minMessage: 'auth.username_length', maxMessage: 'auth.username_length')]
    #[AppAssert\UniqueUsername]
    public string $username;

    #[Assert\NotBlank(message: 'auth.empty_fields')]
    #[Assert\Email(message: 'auth.email_invalid')]
    #[AppAssert\UniqueEmail]
    public string $email;

    #[Assert\NotBlank(message: 'auth.empty_fields')]
    #[Assert\Length(min: 8, minMessage: 'auth.password_length')]
    #[Assert\Regex(pattern: '/[a-z]/', message: 'auth.password_complexity')]
    #[Assert\Regex(pattern: '/[A-Z]/', message: 'auth.password_complexity')]
    #[Assert\Regex(pattern: '/[0-9]/', message: 'auth.password_complexity')]
    public string $password;

    public function __construct(array $data)
    {
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
    }
}
