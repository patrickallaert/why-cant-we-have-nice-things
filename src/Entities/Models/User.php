<?php
namespace History\Entities\Models;

use History\Collection;
use History\Entities\Traits\HasSlug;
use thomaswelton\GravatarLib\Gravatar;

/**
 * @property string     name
 * @property string     slug
 * @property string     full_name
 * @property string     email
 * @property string     company
 * @property string[]   contributions
 * @property int        yes_votes
 * @property int        no_votes
 * @property int        total_votes
 * @property float      approval
 * @property float      success
 * @property float      hivemind
 * @property string     github_id
 * @property string     github_avatar
 * @property string     display_name
 * @property float      negativeness
 * @property Collection approvedRequests
 * @property Collection votes
 * @property Collection requests
 */
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
        'company',
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
        return $this->hasMany(Vote::class)->oldest();
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
     * @param int $size
     *
     * @return string
     */
    public function avatar($size = 200)
    {
        $gravatar = new Gravatar();
        $gravatar->setAvatarSize($size);
        $gravatar->setDefaultImage($this->github_avatar ?: 'retro');

        return $gravatar->buildGravatarURL($this->email);
    }

    /**
     * @return float
     */
    public function getNegativenessAttribute()
    {
        return $this->no_votes / $this->total_votes;
    }
}
