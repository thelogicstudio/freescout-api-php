<?php

declare(strict_types=1);

namespace FreeScout\Api\Chats;

use FreeScout\Api\Customers\Customer;
use FreeScout\Api\Entity\Collection;
use FreeScout\Api\Entity\Hydratable;
use FreeScout\Api\Support\HasCustomer;
use FreeScout\Api\Support\HydratesData;
use FreeScout\Api\Tags\Tag;
use FreeScout\Api\Users\User;

class Chat implements Hydratable
{
    use HydratesData;
    use HasCustomer;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $beaconId;

    /**
     * @var int|null
     */
    private $mailboxId;

    /**
     * @var User|null
     */
    private $assignee;

    /**
     * @var \DateTime|null
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     */
    private $endedAt;

    /**
     * @var string|null
     */
    private $preview;

    /**
     * @var Tag[]|Collection
     */
    private $tags;

    /**
     * @var TimelineEvent[]|Collection
     */
    private $timeline;

    /**
     * @var Event[]|Collection
     */
    private $events;

    public function __construct()
    {
        $this->tags = new Collection();
        $this->timeline = new Collection();
        $this->events = new Collection();
    }

    public function hydrate(array $data, array $embedded = []): void
    {
        $this->id = $data['id'] ?? null;
        $this->beaconId = $data['beaconId'] ?? null;
        $this->mailboxId = $data['mailboxId'] ?? null;
        $this->createdAt = $this->transformDateTime($data['createdAt'] ?? null);
        $this->endedAt = $this->transformDateTime($data['endedAt'] ?? null);
        $this->preview = $data['preview'] ?? null;

        if (isset($data['assignee'])) {
            /** @var User $assignee */
            $assignee = $this->hydrateOne(User::class, $data['assignee']);
            $this->assignee = $assignee;
        }

        if (isset($data['customer'])) {
            $this->hydrateCustomer($data['customer']);
        }

        if (isset($data['tags'])) {
            $this->tags = $this->hydrateMany(Tag::class, $data['tags']);
        }

        if (isset($data['timeline'])) {
            $this->timeline = $this->hydrateMany(TimelineEvent::class, $data['timeline']);
        }

        if (isset($embedded['events'])) {
            $this->events = $this->hydrateMany(Event::class, $embedded['events']);
        }
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBeaconId(): ?string
    {
        return $this->beaconId;
    }

    public function getMailboxId(): ?int
    {
        return $this->mailboxId;
    }

    public function getAssignee(): ?User
    {
        return $this->assignee;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getEndedAt(): ?\DateTime
    {
        return $this->endedAt;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @return Tag[]|Collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @return TimelineEvent[]|Collection
     */
    public function getTimeline(): Collection
    {
        return $this->timeline;
    }

    /**
     * @return Event[]|Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
}
