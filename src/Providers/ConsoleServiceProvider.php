<?php
namespace History\Providers;

use History\Console\Commands\Tinker;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\RequestsGatherer\RequestsGatherer;
use Illuminate\Contracts\Cache\Repository;
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

            // Register commands
            $app->command('tinker', [$this, 'tinker']);
            $app->command('refresh', [$this, 'refresh']);
            $app->command('refresh:stats', [$this, 'refreshStatistics']);

            return $app;
        });
    }

    /**
     * Tinker with the application
     */
    public function tinker()
    {
        $shell = new Shell();
        $shell->setScopeVariables([
            'app' => $this->container,
        ]);

        $shell->run();
    }

    /**
     * Refresh the requests and comments
     *
     * @param OutputInterface $output
     */
    public function refresh(OutputInterface $output)
    {
        // Empty cache
        $cache = $this->container->get(Repository::class);
        $cache->flush();

        // Refresh requests
        $gatherer = $this->container->get(RequestsGatherer::class);
        $gatherer->setOutput($output);
        $gatherer->createRequests();

        // Refresh statistics
        $this->refreshStats($output);
    }

    /**
     * Refresh all statistics
     *
     * @param OutputInterface $output
     */
    public function refreshStats(OutputInterface $output)
    {
        $users    = User::with('votes')->get();
        $requests = Request::with('votes')->get();

        $this->progressIterator($output, $users, function (User $user) {
            $user->computeStatistics();
        });

        $this->progressIterator($output, $requests, function (Request $request) {
            $request->computeStatistics();
        });
    }

    /**
     * @param OutputInterface $output
     * @param array           $entries
     * @param callable        $callback
     */
    protected function progressIterator(OutputInterface $output, $entries, callable $callback)
    {
        $progress = new ProgressBar($output, count($entries));
        foreach ($entries as $entry) {
            $callback($entry);
            $progress->advance();
        }

        $progress->finish();
    }
}
