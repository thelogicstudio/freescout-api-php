<?php

declare(strict_types=1);

namespace FreeScout\Api\Conversations\Threads;

use FreeScout\Api\Entity\Extractable;
use FreeScout\Api\Entity\Hydratable;

class Source implements Extractable, Hydratable
{
    /**
     * @var string
     */
    private $original;

    public function hydrate(array $data, array $embedded = [])
    {
        $this->setOriginal($data['original']);
    }

    /**
     * {@inheritdoc}
     */
    public function extract(): array
    {
        return [
            'original' => $this->getOriginal(),
        ];
    }

    public function getOriginal(): ?string
    {
        return $this->original;
    }

    public function setOriginal(string $original): self
    {
        $this->original = $original;

        return $this;
    }
}
