<?php

declare(strict_types=1);

namespace FreeScout\Api\Chats;

use FreeScout\Api\Entity\Hydratable;
use FreeScout\Api\Support\HydratesData;

class TimelineEvent implements Hydratable
{
    use HydratesData;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var \DateTime|null
     */
    private $timestamp;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $title;

    public function hydrate(array $data, array $embedded = []): void
    {
        $this->type = $data['type'] ?? null;
        $this->timestamp = $this->transformDateTime($data['timestamp'] ?? null);
        $this->url = $data['url'] ?? null;
        $this->title = $data['title'] ?? null;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
