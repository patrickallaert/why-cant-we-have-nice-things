<?php
namespace History;

use Dotenv\Dotenv;
use History\Http\Providers\ErrorsServiceProvider;
use History\Http\Providers\RoutingServiceProvider;
use History\Http\Providers\TwigServiceProvider;
use History\Providers\CacheServiceProvider;
use History\Providers\ConsoleServiceProvider;
use History\Providers\DatabaseServiceProvider;
use History\Providers\GravatarServiceProvider;
use History\Providers\PathsServiceProvider;
use History\Services\RequestsGatherer\RequestsGathererServiceProvider;
use Illuminate\Database\Capsule\Manager;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\Route\Dispatcher;
use League\Route\Http\Exception\NotFoundException;
use League\Route\RouteCollection;
use Silly\Application as Console;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;
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
        ErrorsServiceProvider::class,
        GravatarServiceProvider::class,
    ];

    /**
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: new Container();

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

        try {
            $response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
        } catch (NotFoundException $exception) {
            return $this->container->get(Twig_Environment::class)->display('errors/404.twig');
        }

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
