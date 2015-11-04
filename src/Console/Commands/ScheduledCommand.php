<?php

namespace History\Console\Commands;

use Cron\CronExpression;
use Exception;
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
     * ScheduledCommand constructor.
     *
     * @param Application $console
     */
    public function __construct(Application $console)
    {
        $this->console = $console;
    }

    /**
     * @param bool            $scratch
     * @param bool            $force
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    public function run($scratch, $force, OutputInterface $output)
    {
        $this->wrapOutput($output);

        // Empty cache if needed
        if ($scratch) {
            $this->console->find('cache:clear')->run(new ArrayInput([]), $this->output);
        }

        $schedule = [
            'sync:requests'  => CronExpression::factory('@hourly'),
            'sync:stats'     => CronExpression::factory('@hourly'),
            'sync:internals' => CronExpression::factory('@hourly'),
            'sync:slugs'     => CronExpression::factory('@hourly'),
            'sync:metadata'  => CronExpression::factory('*/2 * * * * *'),
        ];

        /** @var CronExpression $cron */
        foreach ($schedule as $commandName => $cron) {
            $command = $this->console->find($commandName);

            if ($cron->isDue() || $force || $this->console->getContainer()->get('debug')) {
                $command->run(new ArrayInput([]), $this->output);
            } else {
                $nextDueDate = $cron->getNextRunDate()->format('Y-m-d H:i:s');
                $this->output->comment($commandName.' is not due yet (next run at '.$nextDueDate.')');
            }
        }
    }
}
