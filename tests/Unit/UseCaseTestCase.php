<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Domain\CQRS\Command;
use App\Core\Domain\CQRS\Handler;
use App\Core\Domain\CQRS\Query;
use PHPUnit\Framework\TestCase;

abstract class UseCaseTestCase extends TestCase
{
    use UseCaseAssertionsTrait;

    protected ?Handler $useCase = null;

    protected function setUseCase(Handler $useCase): void
    {
        $this->useCase = $useCase;
    }

    /**
     * @dataProvider provideInvalidData
     *
     * @param array<array{propertyPath: string, message: string}> $expectedViolations
     */
    public function testShouldRaiseValidationFailedException(array $expectedViolations, Query|Command $input): void
    {
        self::assertViolations($input, $expectedViolations);
    }

    protected function handle(Command|Query $input): mixed
    {
        if (null === $this->useCase) {
            throw new \RuntimeException('Setup use case before execute it.');
        }

        if (!method_exists($this->useCase, '__invoke')) {
            throw new \RuntimeException('Use case must have __invoke method.');
        }

        $method = new \ReflectionMethod($this->useCase, '__invoke');

        if (null === $method->getReturnType() || !$method->getReturnType() instanceof \ReflectionNamedType) {
            throw new \RuntimeException('Use case must have a return type.');
        }

        if ('void' === $method->getReturnType()->getName()) {
            $this->useCase->__invoke($input);

            return null;
        }

        return $this->useCase->__invoke($input);
    }

    /**
     * @return iterable<array{
     *     expectedViolations: array<array{propertyPath: string,
     *     message: string
     * }>, input: Query|Command}>
     */
    public static function provideInvalidData(): iterable
    {
        return [];
    }
}
