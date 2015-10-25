<?php
namespace History\Entities\Models;

class User extends AbstractModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'full_name',
        'email',
        'contributions',
        'yes_votes',
        'no_votes',
        'total_votes',
        'success',
        'approval',
        'hivemind',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'contributions' => 'array',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function requests()
    {
        return $this->belongsToMany(Request::class);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// ACCESSORS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return float
     */
    public function getNegativenessAttribute()
    {
        return $this->no_votes / $this->total_votes;
    }
}
