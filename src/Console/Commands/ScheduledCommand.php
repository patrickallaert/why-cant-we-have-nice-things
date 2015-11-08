<?php

namespace History\Console\Commands;

use Cron\CronExpression;
use Exception;
use Silly\Application;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

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

        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([
            'debug_formatter' => new DebugFormatterHelper(),
        ]));

        /** @var CronExpression $cron */
        foreach ($schedule as $commandName => $cron) {
            if ($cron->isDue() || $force || $this->console->getContainer()->get('debug')) {
                $this->runCommand($helper, $commandName);
            } else {
                $nextDueDate = $cron->getNextRunDate()->format('Y-m-d H:i:s');
                $this->output->comment($commandName.' is not due yet (next run at '.$nextDueDate.')');
            }
        }
    }

    /**
     * @param ProcessHelper $helper
     * @param string        $commandName
     */
    protected function runCommand(ProcessHelper $helper, $commandName)
    {
        // Define command and input
        $input   = $commandName === 'sync:internals' ? '--size=1000' : '';
        $command = [exec('which php'), 'console', $commandName, '-vvv'];

        // Create process instance
        $process = ProcessBuilder::create($command)->getProcess();
        $process->setTimeout(null);
        $process->setWorkingDirectory(__DIR__.'/../../..');
        $process->setInput($input);

        $helper->run($this->output, $process);
    }
}
