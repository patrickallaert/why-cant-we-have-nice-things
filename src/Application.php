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
use History\Services\Internals\InternalsServiceProvider;
use History\Services\RequestsGatherer\RequestsGathererServiceProvider;
use Illuminate\Database\Capsule\Manager;
use League\Container\Container;
use League\Container\ContainerInterface;
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
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: new Container();
        $this->container->delegate(new ReflectionContainer());

        // Load dotenv file
        $dotenv = new Dotenv(__DIR__.'/..');
        $dotenv->load();
        $this->container->add('debug', getenv('APP_ENV') === 'local');

        // Boot up providers
        foreach ($this->providers as $provider) {
            $this->container->addServiceProvider($provider);
        }

        // Boot database and Whoops
        $this->container->get(Manager::class);
        $this->container->get(Run::class);
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
        /** @var RouteCollection $dispatcher */
        /* @type Request $request */
        $dispatcher = $this->container->get(RouteCollection::class);
        $request    = $this->container->get(Request::class);

        // Convert to PSR7 objects
        $factory  = new DiactorosFactory();
        $request  = $factory->createRequest($request);
        $response = $factory->createResponse(new Response(''));

        try {
            $response = $dispatcher->dispatch($request, $response);
        } catch (NotFoundException $exception) {
            return $this->container->get(Twig_Environment::class)->display('errors/404.twig');
        }

        return (new SapiEmitter())->emit($response);
    }

    /**
     * Run the CLI application.
     */
    public function runConsole()
    {
        $this->container->get(Console::class)->run();
    }
}
