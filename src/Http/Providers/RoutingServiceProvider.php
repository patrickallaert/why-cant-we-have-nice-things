<?php

namespace History\Http\Providers;

use History\Http\Controllers\CompaniesController;
use History\Http\Controllers\EventsController;
use History\Http\Controllers\GroupsController;
use History\Http\Controllers\PagesController;
use History\Http\Controllers\RequestsController;
use History\Http\Controllers\ThreadsController;
use History\Http\Controllers\UsersController;
use History\Http\RouteBindingStrategy;
use History\Services\UrlGenerator;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Route\Route;
use League\Route\RouteCollection;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

class RoutingServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        RouteCollection::class,
        ServerRequestInterface::class,
        UrlGenerator::class,
    ];

    /**
     * @var Route[]
     */
    protected $routes;

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(ServerRequestInterface::class, function () {
            return ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );
        });

        $this->container->share(RouteCollection::class, function () {
            $strategy = new RouteBindingStrategy();
            $strategy->setContainer($this->container);

            $routes = new RouteCollection($this->container);
            $routes->setStrategy($strategy);

            // Register routes
            $this->routes = [
                $routes->get('users', UsersController::class.'::index'),
                $routes->get('/', UsersController::class.'::index'),
                $routes->get('users/{user}', UsersController::class.'::show'),
                $routes->get('about', PagesController::class.'::about'),
                $routes->get('events', EventsController::class.'::index'),
                $routes->get('requests', RequestsController::class.'::index'),
                $routes->get('requests/{request}', RequestsController::class.'::show'),
                $routes->get('companies', CompaniesController::class.'::index'),
                $routes->get('companies/{company}', CompaniesController::class.'::show'),
                $routes->get('groups', GroupsController::class.'::index'),
                $routes->get('groups/{group}', GroupsController::class.'::show'),
                $routes->get('threads', ThreadsController::class.'::index'),
                $routes->get('threads/{thread}', ThreadsController::class.'::show'),
            ];

            return $routes;
        });

        // Since RouteCollection doesn't have a getRoutes we collect the
        // Route instances ourselves and pass them to the UrlGenerator
        $this->container->share(UrlGenerator::class, function () {
            $this->container->get(RouteCollection::class);

            return new UrlGenerator($this->routes);
        });
    }
}
