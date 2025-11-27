<?php

declare(strict_types=1);

namespace FreeScout\Api\Conversations\Threads;

use FreeScout\Api\Conversations\Threads\Support\HasPartiesToBeNotified;
use FreeScout\Api\Support\HasCustomer;

class CustomerThread extends Thread
{
    use HasCustomer,
        HasPartiesToBeNotified;

    public const TYPE = 'customer';

    public static function resourceUrl(int $conversationId): string
    {
        return sprintf('/api/conversations/%d/threads', $conversationId);
    }

    public function getType(): ?string
    {
        return self::TYPE;
    }

    public function hydrate(array $data, array $embedded = [])
    {
        parent::hydrate($data, $embedded);

        if (isset($data['customer']) && is_array($data['customer'])) {
            $this->hydrateCustomer($data['customer']);
        }
    }

    public function extract(): array
    {
        $data = parent::extract();
        $data['type'] = self::TYPE;

        if ($this->hasCustomer()) {
            $data['customer'] = $this->getCustomer()->extract();
        }

        return $data;
    }
}
