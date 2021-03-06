<?php

namespace History\CommandBus;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;

class CommandBusServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        CommandBus::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(CommandBus::class, function () {
            $handlerMiddleware = new CommandHandlerMiddleware(
                new ClassNameExtractor(),
                new ContainerLocator($this->container),
                new HandleInflector()
            );

            return new CommandBus([$handlerMiddleware]);
        });
    }
}
