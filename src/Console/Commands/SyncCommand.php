<?php
namespace History\Console\Commands;

use Exception;
use Illuminate\Contracts\Cache\Repository;
use Silly\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Application
     */
    private $app;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * @var array
     */
    protected $commands = [
        'sync:requests',
        //'sync:internals',
        'sync:stats',
    ];

    /**
     * @param Application $app
     * @param Repository  $cache
     */
    public function __construct(Application $app, Repository $cache)
    {
        $this->app   = $app;
        $this->cache = $cache;
    }

    /**
     * Run the command.
     *
     * @param bool            $scratch
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    public function __invoke($scratch, OutputInterface $output)
    {
        $this->output = new SymfonyStyle(new ArrayInput([]), $output);

        // Empty cache if needed
        if ($scratch) {
            $this->output->writeln('<error>Emptying cache</error>');
            $this->cache->flush();
        }

        // Run sync commands
        foreach ($this->commands as $command) {
            $this->app->find($command)->run(new ArrayInput([$this->output]), $this->output);
        }
    }
}
