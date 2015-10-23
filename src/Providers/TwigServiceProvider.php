<?php
namespace History\Providers;

use League\Container\ServiceProvider;
use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Twig_Environment::class,
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
        $this->container->singleton(Twig_Environment::class, function () {
            $loader = new Twig_Loader_Filesystem(__DIR__.'/../../views');

            return new Twig_Environment($loader, [
                'auto_reload' => true,
                'cache'       => __DIR__.'/../../cache',
            ]);
        });
    }
}
