<?php
namespace History\Entities\Traits;

use History\Entities\Models\Vote;

trait HasVotes
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// STATISTICS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return Collection
     */
    public function getYesVotes()
    {
        return $this->votes->filter(function (Vote $vote) {
            return $vote->vote;
        });
    }

    /**
     * @return Collection
     */
    public function getNoVotes()
    {
        return $this->votes->filter(function (Vote $vote) {
            return !$vote->vote;
        });
    }

    /**
     * @return float
     */
    public function getApproval()
    {
        $totalVotes = $this->votes->count();
        if (!$totalVotes) {
            return 0;
        }

        return $this->getYesVotes()->count() / $totalVotes;
    }

    /**
     * @return float
     */
    public function getHivemind()
    {
        $hivemind = [];
        foreach ($this->votes as $vote) {
            // Consider an RFC passed on 2/3 majority
            $passed     = $vote->request->approval > (2 / 3);
            $user       = (bool) $vote->vote;
            $hivemind[] = $user === $passed;
        }

        $hivemind = count(array_filter($hivemind)) / count($hivemind);
        $hivemind = round($hivemind, 3);

        return $hivemind;
    }
}
