<?php

declare(strict_types=1);

namespace Tests\Component\Symfony\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Exception\ValidationFailedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ApiExceptionSubscriberTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider provideThrowable
     *
     * @param array<mixed> $expectedData
     */
    public function testOnKernelException(\Exception $exception, int $statusCode, array $expectedData): void
    {
        $container = static::getContainer();

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get('event_dispatcher');

        /** @var KernelInterface $kernel */
        $kernel = static::$kernel;

        $event = new ExceptionEvent(
            $kernel,
            Request::create('/', Request::METHOD_GET, server: ['Accept' => 'application/json']),
            HttpKernel::MAIN_REQUEST,
            $exception
        );

        $eventDispatcher->dispatch($event, KernelEvents::EXCEPTION);

        self::assertEquals(new JsonResponse($expectedData, $statusCode, ['Content-Type' => 'application/json']), $event->getResponse());
    }

    public static function provideThrowable(): \Generator
    {
        yield 'Bad request' => [
            'exception' => new BadRequestHttpException('Bad Request'),
            'statusCode' => 400,
            'expectedData' => [
                'message' => 'Bad Request',
            ],
        ];
        yield 'Unauthorized' => [
            'exception' => new UnauthorizedHttpException('', 'Unauthorized'),
            'statusCode' => 401,
            'expectedData' => [
                'message' => 'Unauthorized',
            ],
        ];
        yield 'Access denied' => [
            'exception' => new AccessDeniedHttpException('Access Denied'),
            'statusCode' => 403,
            'expectedData' => [
                'message' => 'Access Denied',
            ],
        ];
        yield 'Not found' => [
            'exception' => new NotFoundHttpException('Not Found'),
            'statusCode' => 404,
            'expectedData' => [
                'message' => 'Not Found',
            ],
        ];
        yield 'Validation failed' => [
            'exception' => new ValidationFailedException(
                new \stdClass(),
                new ConstraintViolationList([
                    new ConstraintViolation(
                        message: 'Error',
                        messageTemplate: 'Error',
                        parameters: [],
                        root: new \stdClass(),
                        propertyPath: 'property',
                        invalidValue: null,
                    ),
                ])
            ),
            'statusCode' => 422,
            'expectedData' => [
                [
                    'propertyPath' => 'property',
                    'message' => 'Error',
                ],
            ],
        ];
        yield 'Other' => [
            'exception' => new \Exception('Internal Server Error'),
            'statusCode' => 500,
            'expectedData' => [
                'message' => 'Internal Server Error',
            ],
        ];
    }
}
