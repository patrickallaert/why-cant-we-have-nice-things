<?php
namespace History\Entities\Models;

use History\Entities\Traits\CanPass;

class Request extends AbstractModel
{
    use CanPass;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
        'condition',
        'approval',
        'passed',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
