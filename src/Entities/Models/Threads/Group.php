<?php

namespace History\Entities\Models\Threads;

use History\Entities\Models\AbstractModel;
use History\Entities\Traits\HasSlugTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends AbstractModel
{
    use HasSlugTrait;

    /**
     * @var array
     */
    protected $fillable = ['name', 'slug'];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return HasMany
     */
    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }
}
