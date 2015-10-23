<?php
namespace History;

use Dotenv\Dotenv;
use History\Providers\CacheServiceProvider;
use History\Providers\ConsoleServiceProvider;
use History\Providers\DatabaseServiceProvider;
use History\Providers\DebugbarServiceProvider;
use History\Providers\ErrorsServiceProvider;
use History\Providers\PathsServiceProvider;
use History\Providers\RoutingServiceProvider;
use History\Providers\TwigServiceProvider;
use History\RequestsGatherer\RequestsGathererServiceProvider;
use Illuminate\Database\Capsule\Manager;
use League\Container\ContainerInterface;
use League\Route\Dispatcher;
use League\Route\RouteCollection;
use Silly\Application as Console;
use Symfony\Component\HttpFoundation\Request;
use Whoops\Run;

class Application
{
    /**
     * @var string
     */
    const NAME = "Why can't we have nice things";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $providers = [
        PathsServiceProvider::class,
        CacheServiceProvider::class,
        RequestsGathererServiceProvider::class,
        RoutingServiceProvider::class,
        TwigServiceProvider::class,
        DatabaseServiceProvider::class,
        ConsoleServiceProvider::class,
        DebugbarServiceProvider::class,
        ErrorsServiceProvider::class,
    ];

    /**
     * Application constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        // Load dotenv file
        $dotenv = new Dotenv(__DIR__.'/..');
        $dotenv->load();

        // Boot up providers
        foreach ($this->providers as $provider) {
            $this->container->addServiceProvider($provider);
        }

        // Boot database and Whoops
        $this->container->get(Manager::class);
        $this->container->get(Run::class);
    }

    /**
     * Run the application.
     */
    public function run()
    {
        /** @var Dispatcher $dispatcher */
        /* @type Request $request */
        $dispatcher = $this->container->get(RouteCollection::class)->getDispatcher();
        $request    = $this->container->get(Request::class);
        $response   = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        return $response->send();
    }

    /**
     * Run the CLI application.
     */
    public function runConsole()
    {
        $this->container->get(Console::class)->run();
    }
}
