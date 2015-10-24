<?php
namespace History\Entities\Models;

use History\Entities\Traits\CanPass;

class Question extends AbstractModel
{
    use CanPass;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'choices',
        'passed',
        'approval',
        'request_id',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get which choice was picked by the majority.
     *
     * @return int
     */
    public function getMajorityChoiceAttribute()
    {
        $majority = $this->votes->groupByCounts('choice')->sort();
        $majority = $majority->keys()->last();

        return $majority;
    }
}
