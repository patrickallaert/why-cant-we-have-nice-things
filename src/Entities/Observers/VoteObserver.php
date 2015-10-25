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
        $type = $vote->choice < $vote->question->choices ? 'up' : 'down';
        $vote->events()->create([
            'type'       => 'vote_'.$type,
            'created_at' => $vote->created_at,
            'updated_at' => $vote->updated_at,
        ]);
    }
}
