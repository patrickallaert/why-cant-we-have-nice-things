<?php

namespace History\Services\Threading\Jobs;

use Collectable;

abstract class Job extends Collectable
{
    /**
     * @var bool
     */
    protected $done = false;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->done;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
