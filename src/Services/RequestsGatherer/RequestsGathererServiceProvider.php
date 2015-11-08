<?php

namespace History\Services\RequestsGatherer;

use League\Container\ServiceProvider\AbstractServiceProvider;

class RequestsGathererServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'gatherer',
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->add('gatherer', function () {
            return $this->container->get(RequestsGatherer::class);
        });
    }
}
