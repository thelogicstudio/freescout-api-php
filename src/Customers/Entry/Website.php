<?php

declare(strict_types=1);

namespace FreeScout\Api\Customers\Entry;

use FreeScout\Api\Assert\Assert;
use FreeScout\Api\Entity\Extractable;
use FreeScout\Api\Entity\Hydratable;

class Website implements Extractable, Hydratable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $value;

    public function hydrate(array $data, array $embedded = [])
    {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }

        $this->setValue($data['value'] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function extract(): array
    {
        return [
            'value' => $this->getValue(),
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Website
    {
        Assert::greaterThan($id, 0);

        $this->id = $id;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue($value): Website
    {
        $this->value = $value;

        return $this;
    }
}
