<?php
namespace History\Services\RequestsGatherer;

use Illuminate\Contracts\Cache\Repository;
use League\Container\ServiceProvider\AbstractServiceProvider;

class RequestsGathererServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        'gatherer',
        RequestsGatherer::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(RequestsGatherer::class, function () {
            return new RequestsGatherer($this->container->get(Repository::class));
        });

        $this->container->add('gatherer', function () {
            return $this->container->get(RequestsGatherer::class);
        });
    }
}
