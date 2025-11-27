<?php

declare(strict_types=1);

namespace FreeScout\Api\Http\Auth;

interface Auth
{
    public function getType(): string;

    public function getPayload(): array;
}
