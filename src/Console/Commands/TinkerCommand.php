<?php

namespace History\Console\Commands;

use Interop\Container\ContainerInterface;
use Psy\Shell;

class TinkerCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * TinkerCommand constructor.
     *
     * @param $container
     * @param $shell
     */
    public function __construct(ContainerInterface $container, Shell $shell)
    {
        $this->container = $container;
        $this->shell = $shell;
    }

    /**
     * Run the command.
     */
    public function __invoke()
    {
        $this->shell->setScopeVariables([
            'app' => $this->container,
        ]);

        $this->shell->run();
    }
}
