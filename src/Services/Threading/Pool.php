<?php

namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use History\Console\HistoryStyle;
use History\Services\Threading\Jobs\CommandBusJob;
use History\Services\Threading\Jobs\Job;
use Pool as NativePool;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Pool extends NativePool
{
    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output = null)
    {
        parent::__construct(5, AutoloadingWorker::class);

        $this->output = $output ?: new HistoryStyle(new ArrayInput([]), new NullOutput());
    }

    /**
     * Reflect and submit a Job to the pool.
     *
     * @param CommandInterface $command
     *
     * @return int|void
     */
    public function handle(CommandInterface $command)
    {
        return parent::submit(new CommandBusJob($command));
    }

    /**
     * Process a pool of jobs.
     *
     * @return array
     */
    public function process()
    {
        $this->output->progressStart(count($this->work));

        // Check the status of jobs until all
        // of them are marked as done
        while (count($this->work)) {
            $this->collect(function (Job $job) {
                $isDone = $job->isDone();

                // Collect results
                if ($isDone) {
                    $this->results[] = $job->getResult();
                    $this->output->progressAdvance();
                }

                return $isDone;
            });
        }

        $this->shutdown();
        $this->output->progressFinish();

        return $this->results;
    }
}
