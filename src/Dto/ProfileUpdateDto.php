<?php

declare(strict_types=1);

namespace GreyPanel\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProfileUpdateDto
{
    #[Assert\Email(message: 'profile.email_invalid')]
    public ?string $email = null;

    #[Assert\Length(min: 8, minMessage: 'profile.password_short')]
    #[Assert\Regex(pattern: '/[a-z]/', message: 'profile.password_weak')]
    #[Assert\Regex(pattern: '/[A-Z]/', message: 'profile.password_weak')]
    #[Assert\Regex(pattern: '/[0-9]/', message: 'profile.password_weak')]
    public ?string $password = null;

    public ?string $passwordConfirm = null;

    public function __construct(array $data)
    {
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->passwordConfirm = $data['password_confirm'] ?? null;
    }
}
