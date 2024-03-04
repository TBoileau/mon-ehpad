<?php

declare(strict_types=1);

namespace Tests\Fixtures\Infrastructure\Symfony\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

final readonly class ValidatorContainer implements ContainerInterface
{
    /**
     * @param array<string, ConstraintValidatorInterface> $services
     */
    public function __construct(private array $services = [])
    {
    }

    public function get($id): object
    {
        if (false === $this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Service "%s" not found.', $id));
        }

        return $this->services[$id];
    }

    public function has($id): bool
    {
        return isset($this->services[$id]);
    }
}
