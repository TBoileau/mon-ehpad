<?php

declare(strict_types=1);

namespace Tests\Integration;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\String\u;
use function Safe\json_encode;

trait ApiAssertionsTrait
{
    public static function assertJsonResponse(mixed $data): void
    {
        if (($client = self::getClient()) === null) {
            throw new \RuntimeException('The client is not set.');
        }

        self::assertResponseHeaderSame('Content-Type', 'application/json');

        /** @var Response $response */
        $response = $client->getResponse();

        self::assertJsonStringEqualsJsonString(json_encode($data), (string) $response->getContent());
    }

    public static function assertMatchesOpenApiResponse(): void
    {
        (new ValidatorBuilder())
            ->fromYamlFile(sprintf('%s/../../%s', __DIR__, $_ENV['OPEN_API_SPEC_PATH']))
            ->getResponseValidator()
            ->validate(self::getOperation(), self::getPsrResponse());
    }

    private static function getPsrResponse(): ResponseInterface
    {
        if (($client = self::getClient()) === null) {
            throw new \RuntimeException('The client is not set.');
        }

        /** @var Response $response */
        $response = $client->getResponse();

        return (new PsrHttpFactory())->createResponse($response);
    }

    private static function getPsrRequest(): RequestInterface
    {
        if (($client = self::getClient()) === null) {
            throw new \RuntimeException('The client is not set.');
        }

        /** @var Request $request */
        $request = $client->getRequest();

        return (new PsrHttpFactory())->createRequest($request);
    }

    private static function getOperation(): OperationAddress
    {
        if (($client = self::getClient()) === null) {
            throw new \RuntimeException('The client is not set.');
        }

        /** @var Request $request */
        $request = $client->getRequest();

        return new OperationAddress($request->getPathInfo(), (string) u($request->getMethod())->lower());
    }
}
