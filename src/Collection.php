<?php
namespace History;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    public function groupByCounts($groupBy)
    {
        return $this->groupBy($groupBy)->map(function ($entries) {
            return $entries->count();
        });
    }
}
