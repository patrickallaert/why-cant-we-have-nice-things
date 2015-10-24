<?php
namespace History\Providers;

use History\Entities\Models\Vote;
use History\Entities\Observers\VoteObserver;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use League\Container\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Manager::class,
    ];

    /**
     * Use the register method to register items with the container via the
     * protected $this->container property or the `getContainer` method
     * from the ContainerAwareTrait.
     */
    public function register()
    {
        $this->container->singleton(Manager::class, function () {
            $capsule = new Manager();
            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => getenv('DB_HOST'),
                'database'  => getenv('DB_DATABASE'),
                'username'  => getenv('DB_USERNAME'),
                'password'  => getenv('DB_PASSWORD'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]);

            // Configure database capsule
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            // Enable query log in local
            if (getenv('APP_ENV') === 'local') {
                $capsule->connection()->enableQueryLog();
            }

            return $capsule;
        });
    }
}
