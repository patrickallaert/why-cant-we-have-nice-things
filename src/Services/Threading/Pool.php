<?php

namespace History\Services\Threading;

use History\CommandBus\CommandInterface;
use History\Console\HistoryStyle;
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
    protected $completed = [];

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output = null)
    {
        parent::__construct(10, Autoloader::class, [__DIR__.'/../../../vendor/autoload.php']);

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
        return parent::submit(new Job($command));
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
        while (!$this->isDone()) {
            $this->collect(function (Job $job) {
                $key = $job->getIdentifier();

                // If we haven't marked this job as
                // completed yet, do it
                if (!array_key_exists($key, $this->completed) && $job->isDone()) {
                    $this->completed[$key] = $job->getResult();
                    $this->output->progressAdvance();
                }
            });
        }

        $this->shutdown();
        $this->output->progressFinish();

        return $this->completed;
    }

    /**
     * @return bool
     */
    protected function isDone()
    {
        return count($this->completed) === (count($this->work) - 1);
    }
}
