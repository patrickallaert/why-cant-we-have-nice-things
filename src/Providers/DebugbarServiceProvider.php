<?php
namespace History\Providers;

use Barryvdh\Debugbar\DataCollector\QueryCollector;
use DebugBar\Bridge\Twig\TwigCollector;
use DebugBar\StandardDebugBar;
use Illuminate\Database\Capsule\Manager;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Twig_Environment;

class DebugbarServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        StandardDebugBar::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(StandardDebugBar::class, function () {
            $twig = $this->container->get(Twig_Environment::class);

            // Create Debugbar
            $debugbar = new StandardDebugBar();
            $debugbar->addCollector(new QueryCollector());
            //$debugbar->addCollector(new TwigCollector($twig));

            // Publish assets
            $renderer = $debugbar->getJavascriptRenderer();
            $renderer->dumpCssAssets('builds/debugbar.css');
            $renderer->dumpJsAssets('builds/debugbar.js');

            // Bind renderer to views
            $twig->addGlobal('debugbar', $renderer);

            // Bind QueryCollector to current connection
            /** @var StandardDebugbar $debugbar */
            $connection = $this->container->get(Manager::class)->connection();
            $connection->listen(function ($query, $bindings, $time) use ($connection) {
                $debugbar  = $this->container->get(StandardDebugBar::class);
                $collector = $debugbar->getCollector('queries');
                $collector->addQuery((string) $query, $bindings, $time, $connection);
            });

            return $debugbar;
        });
    }
}