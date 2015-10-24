<?php
namespace History\Providers;

use League\Container\ServiceProvider;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorsServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Run::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->singleton(Run::class, function () {
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();

            return $whoops;
        });
    }
}
