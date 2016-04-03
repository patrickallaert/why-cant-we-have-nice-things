<?php

namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use League\Tactician\CommandBus;

trait HasAsyncCapabilitiesTrait
{
    /**
     * @var CommandBus
     */
    protected $bus;

    /**
     * @var bool
     */
    protected $async = true;

    /**
     * @param bool $async
     */
    public function setAsync($async)
    {
        $this->async = $async;
    }

    /**
     * @param CommandInterface[] $commands
     *
     * @return array
     */
    protected function dispatchCommands($commands)
    {
        if (!$this->async) {
            foreach ($commands as &$command) {
                $command = $this->bus->handle($command);
            }
        } else {
            $pool = new OutputPool($this->output);
            foreach ($commands as $command) {
                $pool->submitCommand($command);
            }

            $commands = $pool->process();
        }

        return $commands;
    }
}
