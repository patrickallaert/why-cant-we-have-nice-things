<?php
namespace History\Providers;

use History\Console\Commands\Tinker;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\Internals\InternalsSynchronizer;
use History\Services\RequestsGatherer\RequestsGatherer;
use History\Services\StatisticsComputer\StatisticsComputer;
use Illuminate\Contracts\Cache\Repository;
use League\Container\ServiceProvider;
use Psy\Shell;
use Silly\Application;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     */
    public function register()
    {
        $this->container->singleton(Application::class, function () {
            $app = new Application('WhyCantWeHaveNiceThings');

            // Register commands
            $app->command('tinker', [$this, 'tinker']);
            $app->command('sync [--scratch]', [$this, 'sync']);
            $app->command('sync:requests', [$this, 'syncRequests']);
            $app->command('sync:internals', [$this, 'syncInternals']);
            $app->command('sync:stats', [$this, 'syncStats']);

            return $app;
        });
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMANDS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Tinker with the application.
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
     * @param bool            $scratch
     * @param OutputInterface $output
     */
    public function sync($scratch, OutputInterface $output)
    {
        $output = new SymfonyStyle(new ArrayInput([]), $output);

        // Empty cache
        if ($scratch) {
            $output->writeln('<error>Emptying cache</error>');
            $cache = $this->container->get(Repository::class);
            $cache->flush();
        }

        $this->syncRequests($output);
        $this->syncStats($output);
    }

    /**
     * Refresh the requests and votes.
     *
     * @param OutputInterface $output
     */
    public function syncRequests(OutputInterface $output)
    {
        $output->writeln('<comment>Refreshing requests</comment>');
        $gatherer = $this->container->get(RequestsGatherer::class);
        $gatherer->setOutput($output);
        $gatherer->createRequests();
    }

    /**
     * Sync the internals comments
     *
     * @param OutputInterface $output
     */
    public function syncInternals(OutputInterface $output)
    {
        $output->writeln('<comment>Refreshing internal comments</comment>');
        $synchronizer = $this->container->get(InternalsSynchronizer::class);
        $synchronizer->setOutput($output);
        $synchronizer->synchronize();
    }

    /**
     * Refresh all statistics.
     *
     * @param OutputInterface $output
     */
    public function syncStats(OutputInterface $output)
    {
        $output   = new SymfonyStyle(new ArrayInput([]), $output);
        $computer = new StatisticsComputer();

        $users     = User::with('votes', 'requests')->get();
        $questions = Question::with('votes')->get();
        $requests  = Request::with('questions.votes')->get();

        $output->writeln('<comment>Refreshing User statistics</comment>');
        $this->progressIterator($output, $users, function (User $user) use ($computer) {
            $user->update($computer->forUser($user));
        });

        $output->writeln('<comment>Refreshing Question statistics</comment>');
        $this->progressIterator($output, $questions, function (Question $question) use ($computer) {
            $question->update($computer->forQuestion($question));
        });

        $output->writeln('<comment>Refreshing Request statistics</comment>');
        $this->progressIterator($output, $requests, function (Request $request) use ($computer) {
            $request->update($computer->forRequest($request));
        });
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

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
