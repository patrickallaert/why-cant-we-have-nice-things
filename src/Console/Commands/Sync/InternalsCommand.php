<?php

namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Services\Internals\InternalsSynchronizer;
use Symfony\Component\Console\Output\OutputInterface;

class InternalsCommand extends AbstractCommand
{
    /**
     * @var InternalsSynchronizer
     */
    protected $internals;

    /**
     * @param InternalsSynchronizer $internals
     */
    public function __construct(InternalsSynchronizer $internals)
    {
        $this->internals = $internals;
    }

    /**
     * Run the command.
     *
     * @param int             $size
     * @param OutputInterface $output
     */
    public function run($size, OutputInterface $output)
    {
        $this->wrapOutput($output);
        $this->output->title('Refreshing internal comments');

        // Set how many messages to sync
        if ($size) {
            $this->internals->setSize($size);
        }

        $this->internals->setOutput($this->output);
        $this->internals->synchronize();
    }
}
