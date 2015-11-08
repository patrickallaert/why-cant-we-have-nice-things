<?php
namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use History\Services\Threading\Jobs\CommandBusJob;

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
        return parent::submit(new CommandBusJob($command));
    }
}
