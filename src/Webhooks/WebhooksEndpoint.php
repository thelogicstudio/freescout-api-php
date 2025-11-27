<?php

declare(strict_types=1);

namespace FreeScout\Api\Webhooks;

use FreeScout\Api\Endpoint;
use FreeScout\Api\Entity\PagedCollection;

class WebhooksEndpoint extends Endpoint
{
    public const GET_WEBHOOK_URI = '/api/webhooks/%d';
    public const LIST_WEBHOOKS_URI = '/api/webhooks';
    public const CREATE_WEBHOOK_URI = '/api/webhooks';
    public const UPDATE_WEBHOOK_URI = '/api/webhooks/%d';
    public const DELETE_WEBHOOK_URI = '/api/webhooks/%d';
    public const RESOURCE_KEY = 'webhooks';

    public function create(Webhook $webhook): int
    {
        return $this->restClient->createResource(
            $webhook,
            self::CREATE_WEBHOOK_URI
        );
    }

    public function get(int $id): Webhook
    {
        return $this->loadResource(
            Webhook::class,
            sprintf(self::GET_WEBHOOK_URI, $id)
        );
    }

    /**
     * @return Webhook[]|PagedCollection
     */
    public function list(): PagedCollection
    {
        return $this->loadPage(
            Webhook::class,
            self::RESOURCE_KEY,
            self::LIST_WEBHOOKS_URI
        );
    }

    public function update(Webhook $webhook): void
    {
        $this->restClient
            ->updateResource(
                $webhook,
                sprintf(self::UPDATE_WEBHOOK_URI, $webhook->getId())
            );
    }

    public function delete(int $webhookId): void
    {
        $this->restClient->deleteResource(
            sprintf(self::DELETE_WEBHOOK_URI, $webhookId)
        );
    }
}
