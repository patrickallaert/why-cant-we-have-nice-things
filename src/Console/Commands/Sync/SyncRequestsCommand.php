<?php
namespace History\Console\Commands\Sync;

use Cron\CronExpression;
use History\Console\Commands\AbstractCommand;
use History\Console\Commands\foo;
use History\Console\Commands\ScheduledInterface;
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
        $this->output->title('Refreshing requests');
        $this->gatherer->setOutput($this->output);
        $this->gatherer->createRequests();
    }
}
