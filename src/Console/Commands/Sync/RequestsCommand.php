<?php
namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Services\RequestsGatherer\RequestsGatherer;

class RequestsCommand extends AbstractCommand
{
    /**
     * @var RequestsGatherer
     */
    protected $gatherer;

    /**
     * RequestsCommand constructor.
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
        $this->output->title('Refreshing requests');

        $this->gatherer->setOutput($this->output);
        $this->gatherer->createRequests();
    }
}
