<?php
namespace History\Entities\Observers;

use History\Entities\Models\Vote;
use History\StatisticsComputer\StatisticsComputer;

class VoteObserver
{
    /**
     * @param Vote $vote
     */
    public function saved(Vote $vote)
    {
        $computer = new StatisticsComputer();
        
        $vote->user->update($computer->forUser($vote->user));
        $vote->request->update($computer->forRequest($vote->request));
    }
}
