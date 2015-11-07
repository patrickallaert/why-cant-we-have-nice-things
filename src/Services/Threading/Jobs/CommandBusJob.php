<?php

namespace History\Services\Threading\Jobs;

use History\CommandBus\CommandInterface;
use History\Services\Threading\AutoloadingWorker;
use League\Tactician\CommandBus;

class CommandBusJob extends Job
{
    /**
     * @var CommandInterface
     */
    protected $command;

    /**
     * @var AutoloadingWorker
     */
    protected $worker;

    /**
     * @param CommandInterface $command
     */
    public function __construct(CommandInterface $command)
    {
        $this->command = $command;
    }

    /**
     * Run the job with an app context.
     */
    public function run()
    {
        // Get the command bus from the worker
        $bus = $this->worker->getContainer()->get(CommandBus::class);

        // Execute the command and yield its results
        $this->result = $bus->handle($this->command);
        $this->done   = true;
        $this->setGarbage();
    }
}
