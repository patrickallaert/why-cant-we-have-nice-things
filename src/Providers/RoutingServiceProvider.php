<?php
namespace History\Providers;

use History\Http\Controllers\RequestsController;
use History\Http\Controllers\UsersController;
use League\Container\ServiceProvider;
use League\Route\RouteCollection;
use League\Route\Strategy\UriStrategy;
use Symfony\Component\HttpFoundation\Request;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        RouteCollection::class,
        Request::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton(Request::class, function () {
            return Request::createFromGlobals();
        });

        $this->container->singleton(RouteCollection::class, function () {
            $routes = new RouteCollection($this->container);
            $routes->setStrategy(new UriStrategy());

            // Register routes
            $routes->addRoute('GET', '/', UsersController::class.'::index');
            $routes->addRoute('GET', '/users/{user}', UsersController::class.'::show');
            $routes->addRoute('GET', '/requests', RequestsController::class.'::index');

            return $routes;
        });
    }
}
