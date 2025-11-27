<?php

declare(strict_types=1);

namespace FreeScout\Api;

use FreeScout\Api\Chats\ChatsEndpoint;
use FreeScout\Api\Conversations\ConversationsEndpoint;
use FreeScout\Api\Conversations\Threads\Attachments\AttachmentsEndpoint;
use FreeScout\Api\Conversations\Threads\ThreadsEndpoint;
use FreeScout\Api\Customers\CustomersEndpoint;
use FreeScout\Api\Customers\Entry\CustomerEntryEndpoint;
use FreeScout\Api\Http\Authenticator;
use FreeScout\Api\Http\RestClient;
use FreeScout\Api\Mailboxes\MailboxesEndpoint;
use FreeScout\Api\Tags\TagsEndpoint;
use FreeScout\Api\Teams\TeamsEndpoint;
use FreeScout\Api\Users\UsersEndpoint;
use FreeScout\Api\Webhooks\WebhooksEndpoint;
use FreeScout\Api\Workflows\WorkflowsEndpoint;
use Mockery\LegacyMockInterface;

class ApiClient
{
    public const CLIENT_VERSION = '3.2.0';

    public const AVAILABLE_ENDPOINTS = [
        'hs.workflows' => WorkflowsEndpoint::class,
        'hs.webhooks' => WebhooksEndpoint::class,
        'hs.users' => UsersEndpoint::class,
        'hs.threads' => ThreadsEndpoint::class,
        'hs.tags' => TagsEndpoint::class,
        'hs.mailboxes' => MailboxesEndpoint::class,
        'hs.customers' => CustomersEndpoint::class,
        'hs.customerEntry' => CustomerEntryEndpoint::class,
        'hs.conversations' => ConversationsEndpoint::class,
        'hs.attachments' => AttachmentsEndpoint::class,
        'hs.teams' => TeamsEndpoint::class,
        'hs.chats' => ChatsEndpoint::class,
    ];

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var array
     */
    private $container = [];

    public function __construct(RestClient $restClient)
    {
        $this->restClient = $restClient;
    }

    public function mock(string $endpointName): LegacyMockInterface
    {
        $endpointName = 'hs.'.$endpointName;
        $mock = \Mockery::mock(self::AVAILABLE_ENDPOINTS[$endpointName]);

        $this->container[$endpointName] = $mock;

        return $mock;
    }

    public function clearMock(string $endpointName): void
    {
        $endpointName = 'hs.'.$endpointName;
        unset($this->container[$endpointName]);
    }

    public function clearContainer(): void
    {
        $this->container = [];
    }

    public function getAuthenticator(): Authenticator
    {
        return $this->restClient->getAuthenticator();
    }

    public function setApiKey(string $apiKey): ApiClient
    {
        $this->getAuthenticator()
            ->setApiKey($apiKey);

        return $this;
    }

    /**
     * @return mixed
     */
    protected function fetchFromContainer(string $key)
    {
        if (isset($this->container[$key])) {
            return $this->container[$key];
        } else {
            $class = self::AVAILABLE_ENDPOINTS[$key];
            $endpoint = new $class($this->restClient);
            $this->container[$key] = $endpoint;

            return $endpoint;
        }
    }

    public function workflows(): WorkflowsEndpoint
    {
        return $this->fetchFromContainer('hs.workflows');
    }

    public function webhooks(): WebhooksEndpoint
    {
        return $this->fetchFromContainer('hs.webhooks');
    }

    public function users(): UsersEndpoint
    {
        return $this->fetchFromContainer('hs.users');
    }

    public function tags(): TagsEndpoint
    {
        return $this->fetchFromContainer('hs.tags');
    }

    public function mailboxes(): MailboxesEndpoint
    {
        return $this->fetchFromContainer('hs.mailboxes');
    }

    public function customers(): CustomersEndpoint
    {
        return $this->fetchFromContainer('hs.customers');
    }

    public function customerEntry(): CustomerEntryEndpoint
    {
        return $this->fetchFromContainer('hs.customerEntry');
    }

    public function conversations(): ConversationsEndpoint
    {
        return $this->fetchFromContainer('hs.conversations');
    }

    public function chats(): ChatsEndpoint
    {
        return $this->fetchFromContainer('hs.chats');
    }

    public function teams(): TeamsEndpoint
    {
        return $this->fetchFromContainer('hs.teams');
    }

    public function threads(): ThreadsEndpoint
    {
        return $this->fetchFromContainer('hs.threads');
    }

    public function attachments(): AttachmentsEndpoint
    {
        return $this->fetchFromContainer('hs.attachments');
    }
}
