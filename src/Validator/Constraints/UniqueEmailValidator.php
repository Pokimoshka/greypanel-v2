<?php

declare(strict_types=1);

namespace GreyPanel\Validator\Constraints;

use GreyPanel\Interface\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private UserRepositoryInterface $userRepo
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        if ($this->userRepo->findByEmail($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
