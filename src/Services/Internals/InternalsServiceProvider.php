<?php
namespace History\Services\Internals;

use Illuminate\Contracts\Cache\Repository;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Rvdv\Nntp\Client;
use Rvdv\Nntp\Connection\Connection;

class InternalsServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        InternalsSynchronizer::class,
        Internals::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(Internals::class, function () {
            $cache = $this->container->get(Repository::class);
            $connection = new Connection('news.php.net', 119);
            $client = new Client($connection);

            return new Internals($cache, $client);
        });

        $this->container->share(InternalsSynchronizer::class, function () {
            return new InternalsSynchronizer($this->container->get(Internals::class));
        });
    }
}
