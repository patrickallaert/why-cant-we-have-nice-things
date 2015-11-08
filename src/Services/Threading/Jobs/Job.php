<?php

namespace History\Services\Threading\Jobs;

use Collectable;

abstract class Job extends Collectable
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

        $this->setGarbage();
    }
}
