<?php
namespace History\Providers;

use History\Console\Commands\Tinker;
use History\Entities\Models\User;
use League\Container\ServiceProvider;
use Psy\Shell;
use Silly\Application;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Application::class,
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
        $this->container->singleton(Application::class, function () {
            $app = new Application('WhyCantWeHaveNiceThings');

            $app->command('tinker', function () {
                $shell = new Shell();
                $shell->setScopeVariables([
                    'app' => $this->container,
                ]);

                $shell->run();
            });

            $app->command('refresh', function (OutputInterface $output) {
                $users    = User::all();
                $progress = new ProgressBar($output, count($users));
                foreach ($users as $user) {
                    $user->computeStatistics();
                    $progress->advance();
                }

                $progress->finish();
            });

            return $app;
        });
    }
}
