<?php
namespace History;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * @return float|int
     */
    public function average($key = null)
    {
        if (!$this->items) {
            return 0;
        }

        return array_sum($this->items) / count($this->items);
    }

    /**
     * @param string|callable $groupBy
     *
     * @return static
     */
    public function groupByCounts($groupBy)
    {
        return $this->groupBy($groupBy)->map(function ($entries) {
            return $entries->count();
        });
    }

    /**
     * Filter the collection by an attribute
     *
     * @param string $attribute
     *
     * @return static
     */
    public function filterBy($attribute)
    {
        return $this->filter(function ($entry) use ($attribute) {
            return object_get($entry, $attribute);
        });
    }
}
