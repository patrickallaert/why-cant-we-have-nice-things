<?php
namespace History\Services\RequestsGatherer;

use History\Entities\Models\AbstractModel;

interface SynchronizerInterface
{
    /**
     * Synchronize an entity with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize();
}
