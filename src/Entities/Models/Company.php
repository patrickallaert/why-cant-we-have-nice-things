<?php

namespace History\Entities\Models;

use History\Entities\Traits\HasSlug;

/**
 * @property string name
 */
class Company extends AbstractModel
{
    use HasSlug;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'representation',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function votes()
    {
        return $this->hasManyThrough(Vote::class, User::class);
    }
}
