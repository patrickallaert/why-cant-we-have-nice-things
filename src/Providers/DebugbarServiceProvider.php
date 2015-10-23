<?php
namespace History\Providers;

use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\StandardDebugBar;
use League\Container\ServiceProvider;
use Twig_Environment;

class DebugbarServiceProvider extends ServiceProvider
{
    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton(StandardDebugBar::class, function () {
            $debugbar = new StandardDebugBar();
            $debugbar->addCollector(new RequestDataCollector());
            $debugbar->addCollector(new TwigCollector($this->container->get(Twig_Environment::class)));

            return $debugbar;
        });
    }
}
