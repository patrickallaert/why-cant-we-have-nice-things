<?php

namespace History\Entities\Observers;

use History\Entities\Models\User;

class UserObserver
{
    /**
     * @param User $request
     */
    public function saving(User $request)
    {
        $request->sluggify();
    }
}
