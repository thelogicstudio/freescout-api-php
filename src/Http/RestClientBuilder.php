<?php

declare(strict_types=1);

namespace FreeScout\Api\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use FreeScout\Api\Http\Auth\Auth;
use FreeScout\Api\Http\Handlers\AuthenticationHandler;
use FreeScout\Api\Http\Handlers\ClientErrorHandler;
use FreeScout\Api\Http\Handlers\RateLimitHandler;
use FreeScout\Api\Http\Handlers\ValidationHandler;

class RestClientBuilder
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }


    public function build(): RestClient
    {
        $authenticator = $this->getAuthenticator();

        return new RestClient(
            $this->getGuzzleClient(),
            $authenticator
        );
    }

    /**
     * @internal
     */
    protected function getGuzzleClient(): Client
    {
        $options = $this->getOptions();

        return new Client($options);
    }

    protected function getAuthenticator(): Authenticator
    {
        $guzzleConfig = $this->config['guzzle'] ?? [];

        $authenticator = new Authenticator(
            new Client($guzzleConfig),
        );
		$authenticator->setApiKey($this->config['apiKey'] ?? null);

        return $authenticator;
    }

    protected function getOptions(): array
    {
        $guzzleConfig = $this->config['guzzle'] ?? [];
        return array_merge($guzzleConfig, [
			'base_uri' => $this->config['baseUri'] ?? null,
            'handler' => $this->getHandlerStack(),
            'http_errors' => false,
        ]);
    }

    protected function getHandlerStack(): HandlerStack
    {
        $handler = HandlerStack::create();

        $handler->push(new AuthenticationHandler());
        $handler->push(new ClientErrorHandler());
        $handler->push(new RateLimitHandler());
        $handler->push(new ValidationHandler());
        $handler->push(Middleware::retry($this->getRetryDecider()));

        return $handler;
    }

    /**
     * Should we retry this failure?
     *
     * @return \Closure
     */
    protected function getRetryDecider(): callable
    {
        return function (
            $retries,
            Request $request,
            ?Response $response = null,
            ?GuzzleException $exception = null
        ) {
            // Don't retry unless this is a Connection issue
            if (!$exception instanceof ConnectException) {
                return false;
            }

            // Limit the number of retries
            return $retries < 4;
        };
    }
}
