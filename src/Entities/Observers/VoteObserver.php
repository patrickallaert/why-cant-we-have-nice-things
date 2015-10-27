<?php
namespace History\Entities\Observers;

use History\Entities\Models\Vote;

class VoteObserver
{
    /**
     * @param Vote $vote
     */
    public function created(Vote $vote)
    {
        $type = $vote->isPositive() ? 'up' : 'down';
        $vote->registerEvent('vote_'.$type);
    }
}
