<?php

namespace History\CommandBus\Commands;

use History\CommandBus\CommandInterface;
use History\Entities\Models\AbstractModel;

class ComputeStatisticsCommand implements CommandInterface
{
    /**
     * @var AbstractModel
     */
    public $entity;

    /**
     * ComputeStatisticsCommand constructor.
     *
     * @param AbstractModel $entity
     */
    public function __construct(AbstractModel $entity)
    {
        $this->entity = $entity;
    }
}
