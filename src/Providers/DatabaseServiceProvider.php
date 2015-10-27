<?php
namespace History\Providers;

use History\Entities\Models\Comment;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\Entities\Observers\CommentObserver;
use History\Entities\Observers\RequestObserver;
use History\Entities\Observers\VoteObserver;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\FactoryMuffin\Facade;

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
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            // Configure observers
            Vote::observe(new VoteObserver());
            Request::observe(new RequestObserver());
            Comment::observe(new CommentObserver());

            // Enable query log in local
            if ($this->container->get('debug')) {
                $capsule->connection()->enableQueryLog();

                // Seed database if needed
                $this->seedDatabase();
            }

            return $capsule;
        });
    }

    /**
     * Seed the database with dummy data.
     */
    protected function seedDatabase()
    {
        // Load factories if they aren't already
        Facade::loadFactories($this->container->get('paths.factories'));

        if (Request::count()) {
            return;
        }

        User::seed(50);
        $requests = Request::seed(50);
        Question::seed(count($requests) * 3);
        Vote::seed(200);
        Comment::seed(200);
    }
}
