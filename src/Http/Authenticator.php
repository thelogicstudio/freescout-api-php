<?php

declare(strict_types=1);

namespace FreeScout\Api\Http;

use Closure;
use GuzzleHttp\Client;
use FreeScout\Api\Http\Auth\Auth;

class Authenticator
{
    public const CONTENT_TYPE = 'application/json;charset=UTF-8';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setApiKey(string $apiKey): Authenticator
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getAuthHeader(): array
    {
        return [
            'X-FreeScout-API-Key' => $this->apiKey,
        ];
    }
}
