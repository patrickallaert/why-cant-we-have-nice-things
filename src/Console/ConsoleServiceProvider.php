<?php
namespace History\Console;

use History\Console\Commands\CacheClearCommand;
use History\Console\Commands\ScheduledCommand;
use History\Console\Commands\SeedCommand;
use History\Console\Commands\Sync\InternalsCommand;
use History\Console\Commands\Sync\MetadataCommand;
use History\Console\Commands\Sync\RequestsCommand;
use History\Console\Commands\Sync\SlugsCommand;
use History\Console\Commands\Sync\StatsCommand;
use History\Console\Commands\Tinker;
use History\Console\Commands\TinkerCommand;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Silly\Application;

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

            // Register synchronization commands
            $app->command('sync:requests', RequestsCommand::class)->descriptions('Sync the RFCs from the wiki');
            $app->command('sync:internals', InternalsCommand::class)->descriptions('Sync the mailing list');
            $app->command('sync:stats', StatsCommand::class)->descriptions('Sync the entities statistics');
            $app->command('sync:metadata', MetadataCommand::class)->descriptions('Sync additional metadata');
            $app->command('sync:slugs', SlugsCommand::class)->descriptions('Refresh entities slugs');

            // Register maintenance commands
            $app->command('seed', SeedCommand::class)->descriptions('Seed the database with dummy data');
            $app->command('tinker', TinkerCommand::class)->descriptions('Tinker with the app');
            $app->command('cache:clear', CacheClearCommand::class)->descriptions('Empty the cache');
            $app->command('scheduled [--scratch] [--force]', [
                ScheduledCommand::class,
                'run',
            ])->descriptions('Run the scheduled commands', [
                '--scratch' => 'Empty the cache',
                '--force'   => 'Force running of all commands',
            ]);

            return $app;
        });
    }
}
