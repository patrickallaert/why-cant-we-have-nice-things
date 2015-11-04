<?php

namespace History\Providers;

use Illuminate\Cache\FileStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository as IlluminateCache;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Redis\Database;
use League\Container\ServiceProvider\AbstractServiceProvider;

class CacheServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Repository::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->share(FileStore::class, function () {
            return new FileStore(new Filesystem(), $this->container->get('paths.cache'));
        });

        $this->container->share(RedisStore::class, function () {
            $redis = new Database([
                'cluster' => false,
                'default' => [
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
                    'database' => 0,
                ],
            ]);

            return new RedisStore($redis);
        });

        $this->container->share(Repository::class, function () {
            $store = getenv('CACHE_DRIVER') === 'redis' ? RedisStore::class : FileStore::class;
            $store = $this->container->get($store);

            return new IlluminateCache($store);
        });
    }
}
