<?php

namespace History\Services\Threading;

use Collectable;
use History\Application;
use History\CommandBus\ThreadedCommandInterface;
use League\Tactician\CommandBus;

class Job extends Collectable
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
     * @var ThreadedCommandInterface
     */
    private $command;

    /**
     * @param ThreadedCommandInterface $command
     */
    public function __construct(ThreadedCommandInterface $command)
    {
        $this->command = $command;
    }

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

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->command->getIdentifier();
    }

    /**
     * Run the job with an app context.
     */
    public function run()
    {
        // Boot the application and database
        // and everything we might need
        $container = (new Application())->getContainer();
        $bus       = $container->get(CommandBus::class);

        $this->result = $bus->handle($this->command);
        $this->done   = true;
        $this->setGarbage();
    }
}
