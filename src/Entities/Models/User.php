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
        'yes_votes',
        'no_votes',
        'total_votes',
        'success',
        'approval',
        'hivemind',
    ];

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
}
