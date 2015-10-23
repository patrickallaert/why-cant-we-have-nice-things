<?php
namespace History\Entities\Traits;

trait CanPass
{
    /**
     * Did an RFC pass?
     *
     * @return bool
     */
    public function getPassedAttribute()
    {
        $majority = 0.5;
        if (strpos($this->condition, '2/3') !== false) {
            $majority = 2 / 3;
        }

        return $this->approval > $majority;
    }
}
