<?php
namespace History\Entities\Models;

class Request extends AbstractModel
{
    /**
     * @var array
     */
    const STATUS = ['Declined', 'Draft', 'Implemented'];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
        'condition',
        'approval',
        'status',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function authors()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes()
    {
        return $this->hasManyThrough(Vote::class, Question::class);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// ACCESSORS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return array_get(self::STATUS, $this->status);
    }
}
