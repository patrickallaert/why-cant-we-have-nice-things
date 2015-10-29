<?php
namespace History;

use DebugBar\StandardDebugBar;
use Dotenv\Dotenv;
use Exception;
use History\Console\ConsoleServiceProvider;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Interop\Container\ContainerInterface;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Route\Http\Exception\NotFoundException;
use League\Route\RouteCollection;
use Silly\Application as Console;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;
use Whoops\Run;
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
        array_map([$this->container, 'addServiceProvider'], $this->providers);

        // Boot database and Whoops
        $this->container->get(Manager::class);
        $this->container->get(Run::class);

        // Register local providers
        if ($this->container->get('debug')) {
            array_map([$this->container, 'addServiceProvider'], $this->localProviders);
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
        /* @type RouteCollection $dispatcher */
        /* @type Request $request */
        $dispatcher = $this->container->get(RouteCollection::class);
        $request    = $this->container->get(Request::class);

        // Convert to PSR7 objects
        $factory  = new DiactorosFactory();
        $request  = $factory->createRequest($request);
        $response = $factory->createResponse(new Response(''));

        // Collect data for Debugbar before rendering
        if ($this->container->get('debug')) {
            $this->container->get(StandardDebugBar::class);
        }

        try {
            $response = $dispatcher->dispatch($request, $response);
        } catch (Exception $exception) {
            $response = $this->handleError($exception);
        }

        (new SapiEmitter())->emit($response);
    }

    /**
     * Handle an exception.
     *
     * @param Exception $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleError(Exception $exception)
    {
        $factory = new DiactorosFactory();
        $twig    = $this->container->get(Twig_Environment::class);

        switch (true) {
            default:
            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundException:
                $error = $twig->render('errors/404.twig');
                break;
        }

        return $factory->createResponse(new Response($error));
    }

    /**
     * Run the CLI application.
     */
    public function runConsole()
    {
        $this->container->get(Console::class)->run();
    }
}
