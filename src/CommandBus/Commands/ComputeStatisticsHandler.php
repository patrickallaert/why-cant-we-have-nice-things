<?php

namespace History\CommandBus\Commands;

use History\Services\StatisticsComputer\StatisticsComputer;

class ComputeStatisticsHandler
{
    /**
     * @var StatisticsComputer
     */
    protected $statistics;

    /**
     * @param StatisticsComputer $statistics
     */
    public function __construct(StatisticsComputer $statistics)
    {
        $this->statistics = $statistics;
    }

    /**
     * @param ComputeStatisticsCommand $command
     */
    public function handle(ComputeStatisticsCommand $command)
    {
        $stats = $this->statistics->forEntity($command->entity);

        // Update model attributes and save
        $command->entity->fill($stats);
        $command->entity->saveIfDirty();
    }
}
