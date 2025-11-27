<?php

declare(strict_types=1);

namespace FreeScout\Api\Mailboxes;

use FreeScout\Api\Endpoint;
use FreeScout\Api\Entity\PagedCollection;
use FreeScout\Api\Http\Hal\HalPagedResources;
use FreeScout\Api\Http\Hal\HalResource;

class MailboxesEndpoint extends Endpoint
{
    public const GET_MAILBOX_URI = '/api/mailboxes/%d';
    public const LIST_MAILBOXES_URI = '/api/mailboxes';
    public const RESOURCE_KEY = 'mailboxes';

    public function get(int $id, ?MailboxRequest $mailboxRequest = null): Mailbox
    {
        $mailboxResource = $this->restClient->getResource(
            Mailbox::class,
            sprintf(self::GET_MAILBOX_URI, $id));

        return $this->hydrateMailboxWithSubEntities(
            $mailboxResource,
            $mailboxRequest ?: new MailboxRequest()
        );
    }

    /**
     * @return Mailbox[]|PagedCollection
     */
    public function list(?MailboxRequest $mailboxRequest = null): PagedCollection
    {
        return $this->loadMailboxes(
            self::LIST_MAILBOXES_URI,
            $mailboxRequest ?: new MailboxRequest()
        );
    }

    /**
     * @return Mailbox[]|PagedCollection
     */
    private function loadMailboxes(string $uri, MailboxRequest $mailboxRequest): PagedCollection
    {
        /** @var HalPagedResources */
        $mailboxResources = $this->restClient->getResources(Mailbox::class, 'mailboxes', $uri);
        $mailboxes = $mailboxResources->map(function (HalResource $mailboxResource) use ($mailboxRequest) {
            return $this->hydrateMailboxWithSubEntities($mailboxResource, $mailboxRequest);
        });

        return new PagedCollection(
            $mailboxes,
            $mailboxResources->getPageMetadata(),
            $mailboxResources->getLinks(),
            function (string $uri) use ($mailboxRequest) {
                return $this->loadMailboxes($uri, $mailboxRequest);
            },
			$uri
        );
    }

    private function hydrateMailboxWithSubEntities(
        HalResource $mailboxResource,
        MailboxRequest $mailboxRequest
    ): Mailbox {
        $mailboxLoader = new MailboxLoader($this->restClient, $mailboxResource, $mailboxRequest->getLinks());
        $mailboxLoader->load();

        return $mailboxResource->getEntity();
    }
}
