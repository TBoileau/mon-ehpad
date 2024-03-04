<?php

declare(strict_types=1);

namespace App\Security\Domain\Validator;

use App\Security\Domain\Repository\UserRepository;
use App\Security\Domain\ValueObject\Email;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UniqueEmailValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEmail) {
            return;
        }

        if (!$value instanceof Email && !is_string($value)) {
            return;
        }

        $value = $value instanceof Email ? $value : Email::create($value);

        if (!$this->userRepository->isAlreadyUsed($value)) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
