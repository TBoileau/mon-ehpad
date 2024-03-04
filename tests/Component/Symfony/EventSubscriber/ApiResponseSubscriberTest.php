<?php

declare(strict_types=1);

namespace Tests\Component\Symfony\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApiResponseSubscriberTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider provideControllerResult
     */
    public function testShouldCreateJsonResponse(mixed $controllerResult, int $statusCode, mixed $expectedData): void
    {
        $container = static::getContainer();

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get('event_dispatcher');

        /** @var KernelInterface $kernel */
        $kernel = static::$kernel;

        $event = new ViewEvent(
            $kernel,
            Request::create('/', Request::METHOD_GET, server: ['Accept' => 'application/json']),
            HttpKernel::MAIN_REQUEST,
            $controllerResult
        );

        $eventDispatcher->dispatch($event, KernelEvents::VIEW);

        self::assertEquals(new JsonResponse($expectedData, $statusCode, ['Content-Type' => 'application/json']), $event->getResponse());
    }

    public static function provideControllerResult(): \Generator
    {
        yield 'int' => [
            'controllerResult' => 1,
            'statusCode' => 200,
            'expectedData' => 1,
        ];
        yield 'string' => [
            'controllerResult' => 'text',
            'statusCode' => 200,
            'expectedData' => 'text',
        ];
        yield 'float' => [
            'controllerResult' => 1.1,
            'statusCode' => 200,
            'expectedData' => 1.1,
        ];
        yield 'bool' => [
            'controllerResult' => true,
            'statusCode' => 200,
            'expectedData' => true,
        ];
        yield 'array of int' => [
            'controllerResult' => array_fill(0, 5, 1),
            'statusCode' => 200,
            'expectedData' => array_fill(0, 5, 1),
        ];
        yield 'array of string' => [
            'controllerResult' => array_fill(0, 5, 'text'),
            'statusCode' => 200,
            'expectedData' => array_fill(0, 5, 'text'),
        ];
        yield 'array of float' => [
            'controllerResult' => array_fill(0, 5, 1.1),
            'statusCode' => 200,
            'expectedData' => array_fill(0, 5, 1.1),
        ];
        yield 'array of bool' => [
            'controllerResult' => array_fill(0, 5, true),
            'statusCode' => 200,
            'expectedData' => array_fill(0, 5, true),
        ];
        yield 'object' => [
            'controllerResult' => new class () {
                public int $number = 10;
            },
            'statusCode' => 200,
            'expectedData' => ['number' => 10],
        ];
        yield 'array of object' => [
            'controllerResult' => array_fill(0, 5, new class () {
                public int $number = 10;
            }),
            'statusCode' => 200,
            'expectedData' => array_fill(0, 5, ['number' => 10]),
        ];
        yield 'null' => [
            'controllerResult' => null,
            'statusCode' => 204,
            'expectedData' => null,
        ];
    }
}
