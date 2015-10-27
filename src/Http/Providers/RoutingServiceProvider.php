<?php
namespace History\Http\Providers;

use History\Http\Controllers\EventsController;
use History\Http\Controllers\PagesController;
use History\Http\Controllers\RequestsController;
use History\Http\Controllers\UsersController;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Route\RouteCollection;
use League\Route\Strategy\ParamStrategy;
use Symfony\Component\HttpFoundation\Request;

class RoutingServiceProvider extends AbstractServiceProvider
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
     */
    public function register()
    {
        $this->container->share(Request::class, function () {
            return Request::createFromGlobals();
        });

        $this->container->share(RouteCollection::class, function () {
            $routes = new RouteCollection($this->container);

            // Register routes
            $routes->map('GET', '/', UsersController::class.'::index');
            $routes->map('GET', 'users', UsersController::class.'::index');
            $routes->map('GET', 'users/{user}', UsersController::class.'::show');

            $routes->map('GET', 'requests', RequestsController::class.'::index');
            $routes->map('GET', 'requests/{request}', RequestsController::class.'::show');

            $routes->map('GET', 'events', EventsController::class.'::index');

            $routes->map('GET', 'about', PagesController::class.'::about');

            return $routes;
        });
    }
}
