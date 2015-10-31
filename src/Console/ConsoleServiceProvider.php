<?php
namespace History\Console;

use History\Console\Commands\ScheduledCommand;
use History\Console\Commands\SeedCommand;
use History\Console\Commands\Sync\InternalsCommand;
use History\Console\Commands\Sync\MetadataCommand;
use History\Console\Commands\Sync\RequestsCommand;
use History\Console\Commands\Sync\StatsCommand;
use History\Console\Commands\Tinker;
use History\Console\Commands\TinkerCommand;
use Illuminate\Contracts\Cache\Repository;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Silly\Application;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleServiceProvider extends AbstractServiceProvider
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
        $this->container->share(Application::class, function () {
            $app = new Application('WhyCantWeHaveNiceThings');
            $app->useContainer($this->container);

            // Register commands
            $app->command('sync:requests', RequestsCommand::class)->descriptions('Sync the RFCs from the wiki');
            $app->command('sync:internals', InternalsCommand::class)->descriptions('Sync the mailing list');
            $app->command('sync:stats', StatsCommand::class)->descriptions('Sync the entities statistics');
            $app->command('sync:metadata', MetadataCommand::class)->descriptions('Sync additional metadata');

            $app->command('seed', SeedCommand::class)->descriptions('Seed the database with dummy data');
            $app->command('tinker', TinkerCommand::class)->descriptions('Tinker with the app');
            $app->command('scheduled [--scratch]', [ScheduledCommand::class, 'run'])->descriptions('Run the scheduled commands');
            $app->command('cache:clear', function (OutputInterface $output) {
                $this->container->get(Repository::class)->flush();
                $output->writeln('<info>Cache cleared</info>');
            });

            return $app;
        });
    }
}
