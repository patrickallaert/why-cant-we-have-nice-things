<?php
namespace History\Providers;

use League\Container\ServiceProvider;

class PathsServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'paths.cache',
        'paths.builds',
        'paths.views',
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $paths = [
            'cache'  => __DIR__.'/../../cache',
            'views'  => __DIR__.'/../../resources/views',
            'builds' => __DIR__.'/../../public/builds',
        ];

        foreach ($paths as $key => $path) {
            $this->container->add('paths.'.$key, realpath($path));
        }
    }
}
