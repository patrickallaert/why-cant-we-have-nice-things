<?php

namespace History\Entities\Models;

use History\Collection;
use History\Entities\Traits\HasEventsTrait;
use History\Entities\Traits\HasSlugTrait;

/**
 * @property string     name
 * @property string     slug
 * @property string     contents
 * @property string     link
 * @property string     condition
 * @property float      approval
 * @property int        status
 * @property strign     status_label
 * @property Collection votes
 * @property Collection questions
 */
class Request extends AbstractModel
{
    use HasEventsTrait;
    use HasSlugTrait;

    /**
     * @var int
     */
    const DECLINED = 0;

    /**
     * @var int
     */
    const DRAFT = 1;

    /**
     * @var int
     */
    const DISCUSSION = 2;

    /**
     * @var int
     */
    const VOTING = 3;

    /**
     * @var int
     */
    const APPROVED = 4;

    /**
     * @var int
     */
    const INACTIVE = 5;

    /**
     * @var array
     */
    const STATUS = [
        'Declined',
        'In draft',
        'Under discussion',
        'Voting',
        'Implemented',
        'Inactive',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'contents',
        'link',
        'condition',
        'approval',
        'target',
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
