<?php

namespace History\Console\Commands\Sync;

use History\CommandBus\Commands\ComputeStatisticsCommand;
use History\Console\Commands\AbstractCommand;
use History\Entities\Models\Company;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\Threading\Pool;

class StatsCommand extends AbstractCommand
{
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

        $pool = new Pool($this->output);
        foreach ($queries as $query) {
            $entities = $query->get();
            foreach ($entities as $entity) {
                $pool->handle(new ComputeStatisticsCommand($entity));
            }
        }

        $pool->process();
    }
}
