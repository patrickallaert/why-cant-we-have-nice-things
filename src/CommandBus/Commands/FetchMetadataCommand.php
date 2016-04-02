<?php

namespace History\CommandBus\Commands;

use History\CommandBus\CommandInterface;
use History\Entities\Models\User;

class FetchMetadataCommand implements CommandInterface
{
    /**
     * @var User
     */
    public $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
