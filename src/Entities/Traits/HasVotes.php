<?php
namespace History\Entities\Traits;

use History\Entities\Models\Vote;

trait HasVotes
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
        if (!$this->votes->count()) {
            return 0;
        }

        $hivemind = [];
        foreach ($this->votes as $vote) {
            $passed     = (bool) $vote->request->passed;
            $user       = (bool) $vote->vote;
            $hivemind[] = $user === $passed;
        }

        return count(array_filter($hivemind)) / count($hivemind);
    }
}
