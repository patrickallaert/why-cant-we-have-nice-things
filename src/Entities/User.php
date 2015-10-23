<?php
namespace History\Entities;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @return integer
     */
    public function getVotedYesAttribute()
    {
        return $this->votes->filter('voted')->count();
    }

    /**
     * @return integer
     */
    public function getVotedNoAttribute()
    {
        return $this->votes->filter(function (Vote $vote) {
            return !$vote->voted;
        })->count();
    }

    /**
     * @return float
     */
    public function getApprovalAttribute()
    {
        return round($this->voted_yes / $this->votes->count(), 3);
    }
}
