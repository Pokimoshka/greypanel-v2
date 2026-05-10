<?php

declare(strict_types=1);

namespace GreyPanel\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueUsername extends Constraint
{
    public string $message = 'auth.username_taken';

    public function validatedBy(): string
    {
        return 'unique.username';
    }
}
