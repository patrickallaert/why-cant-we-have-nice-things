<?php

namespace History\Entities\Observers;

use History\Entities\Models\Threads\Group;

class GroupObserver
{
    /**
     * @param Group $group
     */
    public function saving(Group $group)
    {
        $group->sluggify();
    }
}
