<?php

namespace History\Services\Github;

use Github\Client;
use Illuminate\Contracts\Cache\Repository;
use League\Container\ServiceProvider\AbstractServiceProvider;

class GithubServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Github::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(Github::class, function () {
            $cache = $this->container->get(Repository::class);
            $client = new Client();
            $client->authenticate(getenv('GITHUB_ID'), getenv('GITHUB_SECRET'), Client::AUTH_URL_CLIENT_ID);

            return new Github($cache, $client);
        });
    }
}
