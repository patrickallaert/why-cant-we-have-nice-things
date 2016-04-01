<?php

namespace History\Services\Threading;

use History\Console\HistoryStyle;
use History\Services\Threading\Jobs\Job;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A threading pool that can communicate its
 * progress through an OutputInterface instance.
 */
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
        // Count number of jobs
        $count = 0;
        foreach ($this->workers as $worker) {
            $count += $worker->getStacked();
        }

        $this->output->progressStart($count);

        // Check the status of jobs until all
        // of them are marked as done
        do {
            $toProcess = $this->collect(function (Job $job) {
                if ($job->done) {
                    $this->output->progressAdvance();
                }

                return $job->done;
            });
        } while ($toProcess);

        $this->shutdown();
        $this->output->progressFinish();
    }
}
