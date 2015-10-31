<?php
namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Services\Internals\InternalsSynchronizer;

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
     */
    public function run()
    {
        $this->output->title('Refreshing internal comments');

        $this->internals->setOutput($this->output);
        $this->internals->synchronize();
    }
}
