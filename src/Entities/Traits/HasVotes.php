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
}
