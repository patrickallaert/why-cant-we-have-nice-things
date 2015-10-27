<?php
namespace History\Http\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorsServiceProvider extends AbstractServiceProvider
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
        $this->container->share(Run::class, function () {
            if (!$this->container->get('debug')) {
                return;
            }

            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();

            return $whoops;
        });
    }
}
