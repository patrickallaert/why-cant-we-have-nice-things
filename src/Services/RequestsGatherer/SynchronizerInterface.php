<?php
namespace History\Services\RequestsGatherer;

interface SynchronizerInterface
{
    /**
     * Synchronize an entity with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize();
}
