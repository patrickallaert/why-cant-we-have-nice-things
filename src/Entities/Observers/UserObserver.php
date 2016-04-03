<?php

namespace History\Entities\Observers;

use History\Entities\Models\User;

class UserObserver
{
    /**
     * @param User $user
     */
    public function saving(User $user)
    {
        $user->sluggify();
    }
}
