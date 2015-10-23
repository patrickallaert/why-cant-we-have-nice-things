<?php
namespace History\Entities\Traits;

use History\Entities\Models\Vote;

trait HasVotes
{
    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return Collection
     */
    public function getYesVotesAttribute()
    {
        return $this->votes->filter(function (Vote $vote) {
            return $vote->vote;
        });
    }

    /**
     * @return Collection
     */
    public function getNoVotesAttribute()
    {
        return $this->votes->filter(function(Vote $vote) {
            return !$vote->vote;
        });
    }

    /**
     * @return float
     */
    public function getApprovalAttribute()
    {
        return round($this->yesVotes->count() / $this->votes->count(), 3);
    }
}
