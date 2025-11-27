<?php

declare(strict_types=1);

namespace FreeScout\Api\Workflows;

use FreeScout\Api\Entity\Extractable;

class WorkflowBatch implements Extractable
{
    /**
     * @var array
     */
    private $conversations;

    /**
     * WorkflowBatch constructor.
     */
    public function __construct(array $conversations = [])
    {
        $this->conversations = $conversations;
    }

    public function extract(): array
    {
        return [
            'conversationIds' => $this->conversations,
        ];
    }
}
