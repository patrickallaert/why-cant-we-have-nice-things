<?php
namespace History\Entities\Traits;

trait CanPass
{
    /**
     * @return float
     */
    public function getMajorityCondition()
    {
        $majority  = 0.5;
        $condition = $this->request ? $this->request->condition : $this->condition;

        if (strpos($condition, '2/3') !== false) {
            $majority = 2 / 3;
        }

        return $majority;
    }

    /**
     * Did an RFC pass?
     *
     * @param float $approval
     *
     * @return bool
     */
    public function hasPassed($approval)
    {
        return $approval > $this->getMajorityCondition();
    }
}
