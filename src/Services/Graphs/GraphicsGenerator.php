<?php
namespace History\Services\Graphs;

use History\Collection;
use History\Entities\Models\User;
use History\Entities\Models\Vote;

class GraphicsGenerator
{
    /**
     * @param User $user
     *
     * @return array
     */
    public function computePositiveness(User $user)
    {
        $total        = 0;
        $positiveness = 0;

        return $this->computeGraph($user->votes, function (Vote $vote) use (&$total, &$positiveness) {
            ++$total;
            $positiveness += (int) $vote->isPositive();

            return [$vote->created_at->format('Y-m'), round($positiveness / $total, 2)];
        });
    }

    /**
     * @param Collection $dataset
     * @param callable   $callback
     *
     * @return array
     */
    protected function computeGraph(Collection $dataset, callable $callback)
    {
        // Gather values
        $values = [];
        $labels = [];
        foreach ($dataset as $key => $value) {
            list($label, $value) = $callback($value);
            $labels[]            = $label;
            $values[]            = $value;
        }

        // Truncate dataset to 10%
        $every = count($labels) * 0.1;
        foreach ($labels as $key => $value) {
            if ($key % $every !== 0) {
                unset($labels[$key]);
                unset($values[$key]);
            }
        }

        return $this->addLineChart($labels, $values);
    }

    /**
     * @param array $labels
     * @param array $values
     *
     * @return array
     */
    protected function addLineChart(array $labels, array $values)
    {
        return [
            'labels'   => array_values($labels),
            'datasets' => [
                [
                    'fillColor'   => '#33cc73',
                    'strokeColor' => '#279B57',
                    'data'        => array_values($values),
                ],
            ],
        ];
    }
}
