<?php

namespace History\Entities\Models;

use History\Collection;
use History\Entities\Traits\Fakable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int            id
 * @property int|string     identifier
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 */
abstract class AbstractModel extends Model
{
    use Fakable;

    /**
     * @return string
     */
    public function getIdentifierAttribute()
    {
        return $this->slug ?: $this->id;
    }

    /**
     * @param array $models
     *
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * Define relationships as set for Twig.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        if (method_exists($this, $key)) {
            return true;
        }

        return parent::__isset($key);
    }

    /**
     * Save only if attributes were changed.
     */
    public function saveIfDirty()
    {
        if ($this->isDirty()) {
            $this->save();
        }
    }
}
