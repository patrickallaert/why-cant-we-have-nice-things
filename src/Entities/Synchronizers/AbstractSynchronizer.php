<?php
namespace History\Entities\Synchronizers;

use Carbon\Carbon;
use DateTime;
use History\Entities\Models\AbstractModel;
use Illuminate\Support\Fluent;

abstract class AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var Fluent
     */
    protected $informations;

    /**
     * Which fields should value
     * current data above new data.
     *
     * @var array
     */
    protected $protected = [];

    /**
     * Whether to update a retrieved
     * entity's timestamps.
     *
     * @var bool
     */
    protected $timestamps = true;

    /**
     * @param array $informations
     */
    public function __construct(array $informations)
    {
        $this->informations = $this->sanitize($informations);
    }

    /**
     * @param array $informations
     *
     * @return Fluent
     */
    protected function sanitize(array $informations)
    {
        return new Fluent($informations);
    }

    /**
     * Get the criterias against which an existing
     * entity will be matched.
     *
     * @return array
     */
    abstract protected function getMatchers();

    /**
     * Get the fields to synchronize on the matched entity.
     *
     * @param AbstractModel $entity
     *
     * @return array
     */
    abstract protected function getSynchronizedFields(AbstractModel $entity);

    /**
     * Synchronize a new or existing entity
     * with a set of data.
     *
     * @return AbstractModel
     */
    public function synchronize()
    {
        // Fetch and synchronize entity
        $entity = $this->retrieveEntity();
        $fields = $this->getSynchronizedFields($entity);
        foreach ($fields as $field => $value) {
            if (in_array($field, $this->protected, true)) {
                $value = $entity->getAttribute($field) ?: $value;
            }

            $entity->setAttribute($field, $value);
        }

        // Update timestamps
        if ($this->timestamps) {
            $this->updateTimestamps($entity, $this->informations->get('timestamps'));
        }

        return $entity;
    }

    /**
     * Synchronize and persist informations to our database.
     *
     * @return AbstractModel
     */
    public function persist()
    {
        $entity = $this->synchronize();
        $entity->saveIfDirty();

        return $entity;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Retrieve an existing entity according to a set of matchers.
     *
     * @return AbstractModel
     */
    protected function retrieveEntity()
    {
        $entity = $this->entity;
        foreach ($this->getMatchers() as $matcher) {
            $matcher = array_filter($matcher);
            if ($matcher && $found = $entity::where($matcher)->first()) {
                return $found;
            }
        }

        return new $entity();
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
        $timestamp = $timestamp ?: Carbon::now();
        if (!$entity->created_at || $timestamp->format('Y-m-d') !== $entity->created_at->format('Y-m-d')) {
            $entity->created_at = $timestamp;
            $entity->updated_at = $timestamp;
        }

        return $entity;
    }
}
