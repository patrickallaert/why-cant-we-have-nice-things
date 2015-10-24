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
}
