<?php

namespace History\CommandsBus\Commands;

use History\CommandBus\Commands\ComputeStatisticsCommand;
use History\CommandBus\Commands\ComputeStatisticsHandler;
use History\Entities\Models\User;
use History\Services\StatisticsComputer\StatisticsComputer;
use History\TestCase;
use Mockery;

class ComputeStatisticsHandlerTest extends TestCase
{
    public function testCanComputeStatisticsForEntity()
    {
        $entity = Mockery::mock(User::class);
        $entity->shouldReceive('fill')->once()->with(['foo' => 'bar']);
        $entity->shouldReceive('saveIfDirty')->once();

        $computer = Mockery::mock(StatisticsComputer::class);
        $computer->shouldReceive('forEntity')->once()->with($entity)->andReturn(['foo' => 'bar']);

        $command = new ComputeStatisticsCommand($entity);
        $handler = new ComputeStatisticsHandler($computer);
        $handler->handle($command);
    }
}
