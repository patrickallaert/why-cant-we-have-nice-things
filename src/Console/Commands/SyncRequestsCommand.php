<?php
namespace History\Console\Commands;

use History\Services\RequestsGatherer\RequestsGatherer;

class SyncRequestsCommand extends AbstractCommand
{
    /**
     * @var RequestsGatherer
     */
    protected $gatherer;

    /**
     * SyncRequestsCommand constructor.
     *
     * @param RequestsGatherer $gatherer
     */
    public function __construct(RequestsGatherer $gatherer)
    {
        $this->gatherer = $gatherer;
    }

    /**
     * Run the command.
     */
    public function run()
    {
        $this->comment('Refreshing requests');
        $this->gatherer->setOutput($this->output);
        $this->gatherer->createRequests();
    }
}
