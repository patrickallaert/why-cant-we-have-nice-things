<?php

namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use History\Services\Threading\Jobs\CommandBusAbstractJob;
use Threaded;

/**
 * Adds command dispatching capabilities
 * to a pool.
 */
trait SubmitsCommandsTrait
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
        return $this->submit(new CommandBusAbstractJob($command));
    }

    /**
     * @param Threaded $job
     *
     * @return mixed
     */
    abstract public function submit(Threaded $job);
}
