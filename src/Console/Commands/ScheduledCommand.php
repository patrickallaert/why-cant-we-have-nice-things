<?php
namespace History\Console\Commands;

use Cron\CronExpression;
use Illuminate\Contracts\Cache\Repository;
use Silly\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduledCommand extends AbstractCommand
{
    /**
     * @var Application
     */
    protected $console;

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * ScheduledCommand constructor.
     *
     * @param Application $console
     * @param Repository  $cache
     */
    public function __construct(Application $console, Repository $cache)
    {
        $this->console = $console;
        $this->cache   = $cache;
    }

    public function run($scratch, OutputInterface $output)
    {
        $this->wrapOutput($output);

        // Empty cache if needed
        if ($scratch) {
            $this->output->error('Emptying cache');
            $this->cache->flush();
        }

        $schedule = [
            'sync:requests'  => CronExpression::factory('@hourly'),
            'sync:stats'     => CronExpression::factory('@hourly'),
            'sync:internals' => CronExpression::factory('@hourly'),
            'sync:metadata'  => CronExpression::factory('*/10 * * * * *'),
        ];

        /** @var CronExpression $cron */
        foreach ($schedule as $commandName => $cron) {
            $command = $this->console->find($commandName);

            if ($cron->isDue() || $this->console->getContainer()->get('debug')) {
                $command->run(new ArrayInput([]), $this->output);
            } else {
                $nextDueDate = $cron->getNextRunDate()->format('Y-m-d H:i:s');
                $this->output->comment($commandName.' is not due yet (next run at '.$nextDueDate.')');
            }
        }
    }
}
