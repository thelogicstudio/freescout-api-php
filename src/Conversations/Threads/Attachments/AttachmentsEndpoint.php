<?php

declare(strict_types=1);

namespace FreeScout\Api\Conversations\Threads\Attachments;

use FreeScout\Api\Endpoint;

class AttachmentsEndpoint extends Endpoint
{
    public function get(int $conversationId, int $attachmentId): Attachment
    {
        $attachmentResource = $this->restClient->getResource(
            Attachment::class,
            sprintf('/api/conversations/%d/attachments/%d/data', $conversationId, $attachmentId)
        );

        return $attachmentResource->getEntity();
    }

    public function create(int $conversationId, int $threadId, Attachment $attachment): ?int
    {
        return $this->restClient->createResource(
            $attachment,
            sprintf('/api/conversations/%d/threads/%d/attachments', $conversationId, $threadId)
        );
    }

    public function delete(int $conversationId, int $attachmentId): void
    {
        $this->restClient->deleteResource(
            sprintf('/api/conversations/%d/attachments/%d', $conversationId, $attachmentId)
        );
    }
}
