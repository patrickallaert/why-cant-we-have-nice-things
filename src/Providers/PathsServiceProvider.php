<?php

namespace History\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;

class PathsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'paths.builds',
        'paths.cache',
        'paths.factories',
        'paths.logs',
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
            'builds'    => __DIR__.'/../../public/builds',
            'factories' => __DIR__.'/../../resources/factories',
            'views'     => __DIR__.'/../../resources/views',
            'cache'     => __DIR__.'/../../storage/cache',
            'logs'      => __DIR__.'/../../storage/logs',
        ];

        foreach ($paths as $key => $path) {
            $this->container->add('paths.'.$key, realpath($path));
        }
    }
}
