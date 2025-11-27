<?php

declare(strict_types=1);

namespace FreeScout\Api\Http;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use FreeScout\Api\ApiClient;
use FreeScout\Api\Entity\Extractable;
use FreeScout\Api\Exception\AuthenticationException;
use FreeScout\Api\Http\Hal\HalDeserializer;
use FreeScout\Api\Http\Hal\HalResource;
use FreeScout\Api\Http\Hal\HalResources;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RestClient
{
    public const CONTENT_TYPE = 'application/json;charset=UTF-8';
    public const CLIENT_USER_AGENT = 'FreeScout PHP API Client/%s (PHP %s)';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Authenticator
     */
    private $authenticator;

    public function __construct(Client $client, Authenticator $authenticator)
    {
        $this->client = $client;
        $this->authenticator = $authenticator;
    }

    public function getAuthenticator(): Authenticator
    {
        return $this->authenticator;
    }

    public function getAuthHeader(): array
    {
        return $this->authenticator->getAuthHeader();
    }

    public function getDefaultHeaders(): array
    {
        return array_merge(
            [
                'Content-Type' => self::CONTENT_TYPE,
                'User-Agent' => sprintf(self::CLIENT_USER_AGENT, ApiClient::CLIENT_VERSION, phpversion()),
            ],
            $this->getAuthHeader()
        );
    }

    public function createResource(Extractable $entity, string $uri): ?int
    {
        $request = new Request(
            'POST',
            $uri,
            $this->getDefaultHeaders(),
            $this->encodeEntity($entity)
        );

        $response = $this->send($request);

        return $response->hasHeader('Resource-ID')
            ? (int) \current($response->getHeader('Resource-ID'))
            : null;
    }

    public function updateResource(Extractable $entity, string $uri): void
    {
        $request = new Request(
            'PUT',
            $uri,
            $this->getDefaultHeaders(),
            $this->encodeEntity($entity)
        );
        $this->send($request);
    }

    public function patchResource(Extractable $entity, string $uri): void
    {
        $request = new Request(
            'PATCH',
            $uri,
            $this->getDefaultHeaders(),
            $this->encodeEntity($entity)
        );
        $this->send($request);
    }

    public function deleteResource(string $uri): void
    {
        $request = new Request(
            'DELETE',
            $uri,
            $this->getDefaultHeaders()
        );
        $this->send($request);
    }

    /**
     * @param Closure|string $entityClass
     */
    public function getResource(
        $entityClass,
        string $uri,
        array $headers = []
    ): HalResource {
        $request = new Request(
            'GET',
            $uri,
            array_merge($this->getDefaultHeaders(), $headers)
        );
        $response = $this->send($request);
        $halDocument = HalDeserializer::deserializeDocument((string) $response->getBody());

        return HalDeserializer::deserializeResource($entityClass, $halDocument);
    }


    /**
     * @param Closure|string $entityClass
     */
    public function getResources($entityClass, string $rel, string $uri): HalResources
    {
        $request = new Request(
            'GET',
            $uri,
            $this->getDefaultHeaders()
        );
        $response = $this->send($request);
        $halDocument = HalDeserializer::deserializeDocument((string) $response->getBody());

        return HalDeserializer::deserializeResources($entityClass, $rel, $halDocument);
    }

    /**
     * @throws \JsonException
     */
    private function encodeEntity(Extractable $entity): string
    {
        return json_encode($entity->extract(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return mixed|ResponseInterface
     */
    private function send(RequestInterface $request)
    {
        $options = [
            'http_errors' => false,
        ];

		return $this->client->send($request, $options);
    }
}
