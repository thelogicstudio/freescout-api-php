<?php

declare(strict_types=1);

namespace FreeScout\Api\Chats;

use FreeScout\Api\Endpoint;

class ChatsEndpoint extends Endpoint
{
    public const CHAT_URI = '/api/chat/v1/%s';
    public const EVENTS_URI = '/api/chat/v1/%s/events';
    public const EVENTS_RESOURCE_KEY = 'events';

    public function get(string $id): Chat
    {
        return $this->loadResource(
            Chat::class,
            sprintf(self::CHAT_URI, $id)
        );
    }

    public function events(string $id)
    {
        return $this->loadPage(
            Event::class,
            self::EVENTS_RESOURCE_KEY,
            sprintf(self::EVENTS_URI, $id)
        );
    }
}
