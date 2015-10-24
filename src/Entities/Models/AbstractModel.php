<?php
namespace History\Entities\Models;

use History\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
    /**
     * @param array $models
     *
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}
