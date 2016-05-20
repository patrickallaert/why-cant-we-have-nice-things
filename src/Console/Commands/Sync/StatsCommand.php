<?php

namespace History\Console\Commands\Sync;

use History\CommandBus\Commands\ComputeStatisticsCommand;
use History\Console\Commands\AbstractCommand;
use History\Entities\Models\Company;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\Threading\OutputPool;
use History\Services\Traits\HasAsyncCapabilitiesTrait;
use League\Tactician\CommandBus;

class StatsCommand extends AbstractCommand
{
    use HasAsyncCapabilitiesTrait;

    /**
     * @param CommandBus $bus
     */
    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Run the command.
     */
    public function run()
    {
        $this->output->title('Refreshing statistics');

        $queries = [
            User::with('votes.question.votes', 'requests'),
            Question::with('votes'),
            Request::with('questions.votes'),
            Company::with('users'),
        ];

        foreach ($queries as $query) {
            $queue = $query->get()->all();
            foreach ($queue as &$entity) {
                $entity = new ComputeStatisticsCommand($entity);
            }

            $this->dispatchCommands($queue);
        }
    }
}
