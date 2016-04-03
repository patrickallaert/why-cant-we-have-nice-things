<?php

namespace History\Services\Threading;

use Exception;
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
            $commands = $this->output->progressIterator($commands);
            $results = [];
            foreach ($commands as $command) {
                try {
                    $results[] = $this->bus->handle($command);
                } catch (Exception $exception) {
                    // Will get caught next sync
                }
            }
        } else {
            $pool = new OutputPool($this->output);
            foreach ($commands as $command) {
                $pool->submitCommand($command);
            }

            $results = $pool->process();
        }

        return $results;
    }
}
