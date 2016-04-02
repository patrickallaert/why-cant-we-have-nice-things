<?php

namespace History\Services\Threading\Jobs;

use History\Services\Threading\AutoloadingWorker;
use Threaded;

abstract class AbstractJob extends Threaded
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
     * @var AutoloadingWorker
     */
    public $worker;

    /**
     * Mark the job as done with a result
     * and mark for collection.
     *
     * @param mixed $result
     */
    protected function markDone($result)
    {
        $this->result = $result;
        $this->done = true;
    }
}
