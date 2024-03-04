<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Symfony\Http\ValueResolver;

use App\Core\Domain\CQRS\Command;
use App\Core\Domain\CQRS\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class MessageValueResolver implements ValueResolverInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @return iterable<Query|Command>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (
            null === $argument->getType()
            || (
                !is_subclass_of($argument->getType(), Query::class)
                && !is_subclass_of($argument->getType(), Command::class)
            )
        ) {
            return [];
        }

        /** @var class-string<Query|Command> $argumentType */
        $argumentType = $argument->getType();

        try {
            return [
                $this->serializer->deserialize(
                    $request->getContent(),
                    $argumentType,
                    'json',
                    [
                        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                    ]
                ),
            ];
        } catch (\Throwable) {
            throw new BadRequestHttpException('Invalid JSON body.');
        }
    }
}
