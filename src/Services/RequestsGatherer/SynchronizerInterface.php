<?php
namespace History\Services\RequestsGatherer;

interface SynchronizerInterface
{
    /**
     * Synchronize an user with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize();
}
