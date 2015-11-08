<?php

namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use History\Console\HistoryStyle;
use History\Services\Threading\Jobs\CommandBusJob;
use History\Services\Threading\Jobs\Job;
use Symfony\Component\Console\Output\OutputInterface;

class OutputPool extends \Pool
{
    use SubmitsCommands;

    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output = null)
    {
        parent::__construct(5, AutoloadingWorker::class);

        $this->output = $output ?: new HistoryStyle();
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
                if ($job->done) {
                    $this->output->progressAdvance();
                }

                return $job->done;
            });
        }

        $this->shutdown();
        $this->output->progressFinish();
    }
}
