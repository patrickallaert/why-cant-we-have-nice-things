<?php
namespace History\Services\Internals;

use Illuminate\Contracts\Cache\Repository;
use League\Container\ServiceProvider;
use Rvdv\Nntp\Client;
use Rvdv\Nntp\Connection\Connection;

class InternalsServiceProvider extends ServiceProvider
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
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton(Internals::class, function () {
            $cache      = $this->container->get(Repository::class);
            $connection = new Connection('news.php.net', 119);

            // Create NNTP client
            $client = new Client($connection);
            $client->connect();

            // Get php.internals group
            $group = $client->group('php.internals')->getResult();

            return new Internals($cache, $client, $group);
        });

        $this->container->singleton(InternalsSynchronizer::class, function () {
            return new InternalsSynchronizer($this->container->get(Internals::class));
        });
    }
}
