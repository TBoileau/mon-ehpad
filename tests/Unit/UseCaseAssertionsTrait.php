<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Domain\CQRS\Command;
use App\Core\Domain\CQRS\Query;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Fixtures\Infrastructure\Symfony\DependencyInjection\ValidatorContainer;

trait UseCaseAssertionsTrait
{
    private static ValidatorInterface $validator;

    /**
     * @param array<string, ConstraintValidatorInterface> $validators
     */
    protected function setValidator(array $validators): void
    {
        $constraintValidatorFactory = new ContainerConstraintValidatorFactory(new ValidatorContainer($validators));

        self::$validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory($constraintValidatorFactory)
            ->enableAttributeMapping()
            ->getValidator();
    }

    /**
     * @param array<array{propertyPath: string, message: string}> $expectedViolations
     */
    public static function assertViolations(Query|Command $input, array $expectedViolations): void
    {
        $violations = self::$validator->validate($input);

        self::assertCount(count($expectedViolations), $violations);

        foreach ($expectedViolations as $index => $expectedViolation) {
            $violation = $violations->get($index);
            self::assertSame(
                [$violation->getPropertyPath(), $violation->getMessage()],
                [$expectedViolation['propertyPath'], $expectedViolation['message']]
            );
        }
    }
}
