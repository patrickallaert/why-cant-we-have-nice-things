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
        $vote->registerEvent($this->getVoteType($vote));
    }

    /**
     * @param Vote $vote
     */
    public function updating(Vote $vote)
    {
        if ($vote->isDirty('choice')) {
            $vote->events()->first()->update([
                'type' => $this->getVoteType($vote)
            ]);
        }
    }

    /**
     * @param Vote $vote
     *
     * @return string
     */
    protected function getVoteType(Vote $vote)
    {
        $type = $vote->isPositive() ? 'up' : 'down';

        return 'vote_'.$type;
    }
}
