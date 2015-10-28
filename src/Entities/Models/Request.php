<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasEvents;

class Request extends AbstractModel
{
    use HasEvents;

    /**
     * @var array
     */
    const STATUS = [
        'Declined',
        'In draft',
        'Under discussion',
        'Voting',
        'Implemented',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'contents',
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

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function versions()
    {
        return $this->hasMany(Version::class)->orderBy('version', 'DESC');
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->oldest();
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rootComments()
    {
        return $this->comments()->whereNull('comment_id');
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// ACCESSORS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param int $status
     *
     * @return string
     */
    public function statusLabel($status)
    {
        return array_get(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        return $this->statusLabel($this->status);
    }
}
