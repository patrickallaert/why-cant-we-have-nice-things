<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use DateTime;
use History\Entities\Models\AbstractModel;
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
        if ($entity->isDirty()) {
            $entity->save();
        }

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

    /**
     * Update the timestamps of an entity if needed.
     *
     * @param AbstractModel        $entity
     * @param string|DateTime|null $timestamp
     *
     * @return AbstractModel
     */
    protected function updateTimestamps(AbstractModel $entity, $timestamp = null)
    {
        $timestamp = $timestamp ?: new DateTime();
        if (!$entity->created_at || $timestamp->format('Y-m-d') !== $entity->created_at->format('Y-m-d')) {
            $entity->created_at = $timestamp;
            $entity->updated_at = $timestamp;
        }

        return $entity;
    }
}
