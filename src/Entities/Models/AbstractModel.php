<?php
namespace History\Entities\Models;

use History\Collection;
use History\Entities\Traits\Fakable;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
    use Fakable;

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
}
