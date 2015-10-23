<?php
namespace History\Entities\Observers;

use History\Entities\Models\Vote;

class VoteObserver
{
    /**
     * @param Vote $vote
     */
    public function saved(Vote $vote)
    {
        $vote->user->computeStatistics();
        $vote->request->update([
           'approval' => $vote->request->getApp
        ]);
    }
}
