<?php

declare(strict_types=1);

namespace FreeScout\Api\Entity;

interface Extractable
{
    public function extract(): array;
}
