<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasSlug;

class User extends AbstractModel
{
    use HasSlug;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'full_name',
        'email',
        'contributions',
        'yes_votes',
        'no_votes',
        'total_votes',
        'success',
        'approval',
        'hivemind',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'contributions' => 'array',
    ];

    /**
     * @return string
     */
    public function getSlugSource()
    {
        return $this->getDisplayNameAttribute();
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

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

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function approvedRequests()
    {
        return $this->requests()->where('status', Request::APPROVED);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// ACCESSORS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return head(array_filter([$this->name, $this->full_name, $this->email]));
    }

    /**
     * @return float
     */
    public function getNegativenessAttribute()
    {
        return $this->no_votes / $this->total_votes;
    }
}
