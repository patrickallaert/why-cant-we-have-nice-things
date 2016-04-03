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
        Internals::class,
        Client::class,
        MailingListArticleCleaner::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(MailingListArticleCleaner::class, function () {
            return new MailingListArticleCleaner();
        });

        $this->container->share(Client::class, function () {
            $connection = new Connection('news.php.net', 119);
            $client = new Client($connection);

            return $client;
        });

        $this->container->share(Internals::class, function () {
            return new Internals(
                $this->container->get(Repository::class),
                $this->container->get(Client::class),
                $this->container->get(MailingListArticleCleaner::class)
            );
        });
    }
}
