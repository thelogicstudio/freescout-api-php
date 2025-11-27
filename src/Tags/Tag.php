<?php

declare(strict_types=1);

namespace FreeScout\Api\Tags;

use FreeScout\Api\Entity\Extractable;
use FreeScout\Api\Entity\Hydratable;
use FreeScout\Api\Support\ExtractsData;
use FreeScout\Api\Support\HydratesData;

class Tag implements Extractable, Hydratable
{
    use ExtractsData,
        HydratesData;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    public function hydrate(array $data, array $embedded = [])
    {
        $this->setId($data['id'] ?? null);

        // API endpoints sometimes use "tag" and sometimes "name" so we'll hydrate assuming it could be either
        if (isset($data['name'])) {
            $this->setName($data['name'] ?? null);
        } elseif (isset($data['tag'])) {
            $this->setName($data['tag']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function extract(): array
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];

        return $data;
    }

    /**
     * @param string|null $id
     *
     * @return Tag
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $name
     *
     * @return Tag
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
