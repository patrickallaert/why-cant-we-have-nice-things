<?php

namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Entities\Models\AbstractModel;
use History\Entities\Models\Company;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\StatisticsComputer\StatisticsComputer;
use History\Services\Threading\Jobs\ClosureJob;
use History\Services\Threading\Pool;
use Illuminate\Database\Eloquent\Collection;

class StatsCommand extends AbstractCommand
{
    /**
     * @var StatisticsComputer
     */
    protected $computer;

    /**
     * @param StatisticsComputer $computer
     */
    public function __construct(StatisticsComputer $computer)
    {
        $this->computer = $computer;
    }

    /**
     * Run the command.
     */
    public function run()
    {
        $pool = new Pool($this->output);
        $this->output->title('Refreshing statistics');

        $entities = [
            User::with('votes.question.votes', 'requests')->get(),
            Question::with('votes')->get(),
            Request::with('questions.votes')->get(),
            Company::with('users')->get(),
        ];

        foreach ($entities as $collection) {
            $this->submitToPool($pool, $collection);
        }

        return $pool->process();
    }

    /**
     * @param Pool       $pool
     * @param Collection $entities
     */
    protected function submitToPool(Pool $pool, Collection $entities)
    {
        $updateEntity = function (StatisticsComputer $computer, AbstractModel $entity) {
            $entity->fill($computer->forEntity($entity))->saveIfDirty();
        };

        foreach ($entities as $entity) {
            $pool->submit(new ClosureJob($updateEntity, [$this->computer, $entity]));
        }
    }
}
