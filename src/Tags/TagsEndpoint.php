<?php

declare(strict_types=1);

namespace FreeScout\Api\Tags;

use FreeScout\Api\Endpoint;
use FreeScout\Api\Entity\PagedCollection;

class TagsEndpoint extends Endpoint
{
    public const LIST_TAGS_URI = '/api/tags';
    public const RESOURCE_KEY = 'tags';

    public function list(): PagedCollection
    {
        return $this->loadPage(
            Tag::class,
            self::RESOURCE_KEY,
            self::LIST_TAGS_URI
        );
    }
}
