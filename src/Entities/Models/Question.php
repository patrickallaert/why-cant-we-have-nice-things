<?php
namespace History\Entities\Models;

use History\Collection;

/**
 * @property string     name
 * @property string[]   choices
 * @property bool       passed
 * @property float      approval
 * @property Collection votes
 * @property Request    request
 */
class Question extends AbstractModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'choices',
        'passed',
        'approval',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'choices' => 'array',
        'passed'  => 'boolean',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * @codeCoverageIgnore
     *
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
        $choices = $this->votes->lists('choice')->all();
        $choices = array_count_values($choices);
        arsort($choices);

        return head(array_keys($choices));
    }
}
