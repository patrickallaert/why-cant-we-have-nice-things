<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Services\RequestsGatherer\SynchronizerInterface;

abstract class AbstractSynchronizer implements SynchronizerInterface
{
    /**
     * @var array
     */
    protected $informations;

    /**
     * @param array $informations
     */
    public function __construct(array $informations)
    {
        $this->informations = $informations;
    }

    /**
     * Synchronize and persist informations to our database.
     *
     * @return \History\Entities\Models\AbstractModel
     */
    public function persist()
    {
        $entity = $this->synchronize();
        $entity->save();

        return $entity;
    }

    /**
     * @param string $information
     *
     * @return mixed
     */
    protected function get($information)
    {
        return array_get($this->informations, $information);
    }
}
