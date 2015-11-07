<?php

namespace History\Services\Threading;

use Collectable;
use History\Application;

abstract class Job extends Collectable
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @var bool
     */
    protected $done = false;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * Job constructor.
     */
    public function __construct()
    {
        $this->identifier = uniqid();
    }

    /**
     * @return boolean
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

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param array $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Run the job with an app context.
     */
    public function run()
    {
        // Boot the application and database
        // and everything we might need
        $app = new Application();

        $this->result = $this->fire();
        $this->done = true;
        $this->setGarbage();
    }

    /**
     * Fire the job.
     *
     * @return mixed
     */
    abstract public function fire();
}
