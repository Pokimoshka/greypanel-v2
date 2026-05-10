<?php

declare(strict_types=1);

namespace GreyPanel\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEmail extends Constraint
{
    public string $message = 'auth.email_taken';

    public function validatedBy(): string
    {
        return 'unique.email';
    }
}
