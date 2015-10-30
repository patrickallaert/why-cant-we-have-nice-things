<?php
namespace History;

use DebugBar\StandardDebugBar;
use Dotenv\Dotenv;
use History\Console\ConsoleServiceProvider;
use History\Http\Middlewares\LeagueRouteMiddleware;
use History\Http\Middlewares\WhoopsMiddleware;
use History\Http\Providers\ErrorsServiceProvider;
use History\Http\Providers\RoutingServiceProvider;
use History\Http\Providers\TwigServiceProvider;
use History\Providers\CacheServiceProvider;
use History\Providers\DatabaseServiceProvider;
use History\Providers\DebugbarServiceProvider;
use History\Providers\GravatarServiceProvider;
use History\Providers\PathsServiceProvider;
use History\Services\Internals\InternalsServiceProvider;
use History\Services\RequestsGatherer\RequestsGathererServiceProvider;
use Illuminate\Database\Capsule\Manager;
use Interop\Container\ContainerInterface;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Http\Message\ServerRequestInterface;
use Relay\RelayBuilder;
use Silly\Application as Console;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * @method get
 */
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
        InternalsServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $localProviders = [
        DebugbarServiceProvider::class,
    ];

    /**
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        // Configure container
        $this->container = $container ?: new Container();
        $this->container->delegate(new ReflectionContainer());
        $this->container->share(ContainerInterface::class, $this->container);

        // Load dotenv file
        $dotenv = new Dotenv(__DIR__.'/..');
        $dotenv->load();

        // Bind global debug mode
        $debug = in_array(getenv('APP_ENV'), ['local', 'testing'], true);
        $this->container->add('debug', $debug);

        // Register providers
        $this->registerProviders();
    }

    /**
     * Register the application's service providers.
     */
    protected function registerProviders()
    {
        // Register providers
        array_walk($this->providers, [$this->container, 'addServiceProvider']);
        $this->container->get(Manager::class);

        // Register local providers
        if ($this->container->get('debug')) {
            array_walk($this->localProviders, [$this->container, 'addServiceProvider']);
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Run the application.
     */
    public function run()
    {
        // Create Request and Response
        $request  = $this->container->get(ServerRequestInterface::class);
        $response = new Response();

        $middlewares = [
            LeagueRouteMiddleware::class,
        ];

        // Collect data for Debugbar before rendering
        $debug = $this->container->get('debug');
        if ($debug) {
            $this->container->get(StandardDebugBar::class);
            array_unshift($middlewares, WhoopsMiddleware::class);
        }

        // Apply middlewares
        $builder  = new RelayBuilder([$this->container, 'get']);
        $relay    = $builder->newInstance($middlewares);
        $response = $relay($request, $response);

        (new SapiEmitter())->emit($response);
    }

    /**
     * Run the CLI application.
     */
    public function runConsole()
    {
        $this->container->get(Console::class)->run();
    }
}
