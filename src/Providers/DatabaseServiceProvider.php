<?php

namespace History\Providers;

use History\Entities\Models\Company;
use History\Entities\Models\Request;
use History\Entities\Models\Threads\Comment;
use History\Entities\Models\Threads\Group;
use History\Entities\Models\Threads\Thread;
use History\Entities\Models\User;
use History\Entities\Models\Version;
use History\Entities\Models\Vote;
use History\Entities\Observers\CommentObserver;
use History\Entities\Observers\CompanyObserver;
use History\Entities\Observers\GroupObserver;
use History\Entities\Observers\RequestObserver;
use History\Entities\Observers\ThreadObserver;
use History\Entities\Observers\UserObserver;
use History\Entities\Observers\VersionObserver;
use History\Entities\Observers\VoteObserver;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use League\Container\ServiceProvider\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider
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
        $this->container->share(Manager::class, function () {
            $database = $this->createDatabase();

            // Configure observers
            Comment::observe(new CommentObserver());
            Company::observe(new CompanyObserver());
            Group::observe(new GroupObserver());
            Request::observe(new RequestObserver());
            Thread::observe(new ThreadObserver());
            User::observe(new UserObserver());
            Version::observe(new VersionObserver());
            Vote::observe(new VoteObserver());

            return $database;
        });
    }

    /**
     * Create the database and configure it.
     *
     * @return Manager
     */
    public function createDatabase()
    {
        $capsule = new Manager();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        // Configure database capsule
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }
}
