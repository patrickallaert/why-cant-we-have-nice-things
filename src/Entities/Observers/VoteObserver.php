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
        $vote->events()->create([
            'type'       => 'vote',
            'created_at' => $vote->created_at,
            'updated_at' => $vote->updated_at,
        ]);
    }
}
