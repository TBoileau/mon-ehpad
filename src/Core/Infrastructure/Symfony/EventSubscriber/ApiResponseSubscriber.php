<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Symfony\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ApiResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => 'onKernelView'];
    }

    public function onKernelView(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (null === $result) {
            $event->setResponse(new JsonResponse(status: Response::HTTP_NO_CONTENT));

            return;
        }

        $event->setResponse(
            new JsonResponse(
                $this->serializer->serialize($result, 'json'),
                Response::HTTP_OK,
                [],
                true
            )
        );
    }
}
