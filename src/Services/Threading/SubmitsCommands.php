<?php

namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use History\Services\Threading\Jobs\CommandBusJob;
use Threaded;

/**
 * Adds command dispatching capabilities
 * to a pool.
 */
trait SubmitsCommands
{
    /**
     * Submit a command to the pool.
     *
     * @param CommandInterface $command
     *
     * @return int|void
     */
    public function submitCommand(CommandInterface $command)
    {
        return $this->submit(new CommandBusJob($command));
    }

    /**
     * @param Threaded $job
     *
     * @return mixed
     */
    abstract public function submit($job);
}
