<?php

namespace History\CommandBus;

use Interop\Container\ContainerInterface;
use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;

class ContainerLocator implements HandlerLocator
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieves the handler for a specified command.
     *
     * @param string $commandName
     *
     * @throws MissingHandlerException
     *
     * @return object
     */
    public function getHandlerForCommand($commandName)
    {
        $handler = preg_replace('/Command$/', 'Handler', $commandName);

        return $this->container->get($handler);
    }
}
