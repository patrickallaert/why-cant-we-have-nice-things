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
        $user       = $vote->user;
        $yesVotes   = $user->votes()->where('vote', true)->count();
        $totalVotes = $user->votes()->count();

        $user->update([
            'yes_votes'   => $yesVotes,
            'no_votes'    => $user->votes()->where('vote', false)->count(),
            'total_votes' => $totalVotes,
            'aproval'     => $totalVotes ? $yesVotes / $totalVotes : 0,
            'hivemind'    => $user->computeHivemind(),
        ]);
    }
}
