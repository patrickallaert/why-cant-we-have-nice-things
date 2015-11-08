<?php

namespace History\Services\Threading\Jobs;

use Threaded;

abstract class Job extends Threaded
{
    /**
     * @var bool
     */
    public $done = false;

    /**
     * @var mixed
     */
    public $result;

    /**
     * Mark the job as done with a result
     * and mark for collection.
     *
     * @param mixed $result
     */
    protected function markDone($result)
    {
        $this->result = $result;
        $this->done   = true;
    }
}
