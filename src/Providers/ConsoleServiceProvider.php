<?php
namespace History\Providers;

use History\Console\Commands\SyncCommand;
use History\Console\Commands\SyncRequestsCommand;
use History\Console\Commands\SyncStatsCommand;
use History\Console\Commands\TestCommand;
use History\Console\Commands\Tinker;
use History\Console\Commands\TinkerCommand;
use Illuminate\Contracts\Cache\Repository;
use Interop\Container\ContainerInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Silly\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            $app->command('tinker', TinkerCommand::class)->descriptions('Tinker with the app');
            $app->command('sync [--scratch]', SyncCommand::class)->descriptions('Sync all the things');
            $app->command('sync:requests', SyncRequestsCommand::class)->descriptions('Sync the RFCs from the wiki');
            $app->command('sync:internals', SyncInternalsCommandI::class)->descriptions('Sync the mailing list');
            $app->command('sync:stats', SyncStatsCommand::class)->descriptions('Sync the entities statistics');

            return $app;
        });
    }
}
