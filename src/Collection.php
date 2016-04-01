<?php

namespace History;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * @param string|null $key
     *
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
     * Filter the collection by an attribute.
     *
     * @param string $attribute
     *
     * @return static
     */
    public function filterBy($attribute)
    {
        return $this->filter(function ($entry) use ($attribute) {
            return data_get($entry, $attribute);
        });
    }
}
