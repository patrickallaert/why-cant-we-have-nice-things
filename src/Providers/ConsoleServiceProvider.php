<?php
namespace History\Providers;

use History\Console\Commands\Tinker;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
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
            $app->command('refresh [--scratch]', [$this, 'refresh']);
            $app->command('stats', [$this, 'refreshStats']);

            return $app;
        });
    }

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
     * Refresh the requests and comments.
     *
     * @param bool            $scratch
     * @param OutputInterface $output
     */
    public function refresh($scratch, OutputInterface $output)
    {
        $output = new SymfonyStyle(new ArrayInput([]), $output);

        // Empty cache
        if ($scratch) {
            $output->writeln('<error>Emptying cache</error>');
            $cache = $this->container->get(Repository::class);
            $cache->flush();
        }

        // Refresh requests
        $output->writeln('<comment>Refreshing requests</comment>');
        $gatherer = $this->container->get(RequestsGatherer::class);
        $gatherer->setOutput($output);
        $gatherer->createRequests();

        // Refresh statistics
        $this->refreshStats($output);
    }

    /**
     * Refresh all statistics.
     *
     * @param OutputInterface $output
     */
    public function refreshStats(OutputInterface $output)
    {
        $output = new SymfonyStyle(new ArrayInput([]), $output);
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
