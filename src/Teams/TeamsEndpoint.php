<?php

declare(strict_types=1);

namespace FreeScout\Api\Teams;

use FreeScout\Api\Endpoint;
use FreeScout\Api\Entity\PagedCollection;
use FreeScout\Api\Users\User;
use FreeScout\Api\Users\UsersEndpoint;

class TeamsEndpoint extends Endpoint
{
    public const LIST_USERS_URI = '/api/teams';
    public const RESOURCE_KEY = 'teams';

    /**
     * Get a list of teams.
     *
     * @return Team[]|PagedCollection
     */
    public function list(): PagedCollection
    {
        return $this->loadPage(
            Team::class,
            self::RESOURCE_KEY,
            self::LIST_USERS_URI
        );
    }

    /**
     * Get the members of a team.
     *
     * @return User[]|PagedCollection
     */
    public function members(int $teamId): PagedCollection
    {
        return $this->loadPage(
            User::class,
            UsersEndpoint::RESOURCE_KEY,
            sprintf(self::LIST_USERS_URI.'/%d/members', $teamId)
        );
    }
}
