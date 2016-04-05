<?php

namespace History\Services\Traits;

use Exception;
use History\CommandBus\CommandInterface;
use History\Services\Threading\OutputPool;
use League\Tactician\CommandBus;

trait HasAsyncCapabilitiesTrait
{
    use HasOutputTrait;

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
        $results = $this->isAsync()
            ? $this->dispatchAsynchronously($commands)
            : $this->dispatchSynchronously($commands);

        return $results;
    }

    /**
     * @param CommandInterface[] $commands
     *
     * @return array
     */
    protected function dispatchSynchronously($commands)
    {
        $results = [];
        $commands = $this->output->progressIterator($commands);
        foreach ($commands as $command) {
            try {
                $results[] = $this->bus->handle($command);
            } catch (Exception $exception) {
                // Will get caught next sync
            }
        }

        return $results;
    }

    /**
     * @param CommandInterface[] $commands
     *
     * @return array
     */
    protected function dispatchAsynchronously($commands)
    {
        $pool = new OutputPool($this->output);
        foreach ($commands as $command) {
            $pool->submitCommand($command);
        }

        return $pool->process();
    }

    /**
     * @return bool
     */
    protected function isAsync(): bool
    {
        return $this->async && extension_loaded('pthreads');
    }
}
