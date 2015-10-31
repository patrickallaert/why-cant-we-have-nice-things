<?php
namespace History;

use DebugBar\StandardDebugBar;
use Dotenv\Dotenv;
use Franzl\Middleware\Whoops\Middleware as WhoopsMiddleware;
use History\Console\ConsoleServiceProvider;
use History\Http\Middlewares\ErrorsMiddleware;
use History\Http\Middlewares\LeagueRouteMiddleware;
use History\Http\Providers\RoutingServiceProvider;
use History\Http\Providers\TwigServiceProvider;
use History\Providers\CacheServiceProvider;
use History\Providers\DatabaseServiceProvider;
use History\Providers\DebugbarServiceProvider;
use History\Providers\GravatarServiceProvider;
use History\Providers\PathsServiceProvider;
use History\Services\Github\GithubServiceProvider;
use History\Services\Internals\InternalsServiceProvider;
use History\Services\RequestsGatherer\RequestsGathererServiceProvider;
use Illuminate\Database\Capsule\Manager;
use Interop\Container\ContainerInterface;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Http\Message\ServerRequestInterface;
use Psr7Middlewares\Middleware\When;
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
     * @var Container
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
        GravatarServiceProvider::class,
        InternalsServiceProvider::class,
        GithubServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $localProviders = [
        DebugbarServiceProvider::class,
    ];

    /**
     * @param Container|null $container
     */
    public function __construct(Container $container = null)
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
     * Run the application.
     */
    public function run()
    {
        // Create Request and Response
        $request  = $this->container->get(ServerRequestInterface::class);
        $response = new Response();

        $debug = $this->container->get('debug');
        $this->container->get(StandardDebugBar::class);
        $middlewares = [
            new When($debug, new WhoopsMiddleware()),
            new When($debug, $this->container->get(ErrorsMiddleware::class)),
            LeagueRouteMiddleware::class,
        ];

        // Apply middlewares
        $builder = new RelayBuilder(function ($callable) {
            return is_string($callable) ? $this->container->get($callable) : $callable;
        });

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
