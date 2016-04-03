<?php

namespace History\Entities\Observers;

use History\Entities\Models\Thread;

class ThreadObserver
{
    /**
     * @param Thread $thread
     */
    public function saving(Thread $thread)
    {
        $thread->sluggify();
    }
}
