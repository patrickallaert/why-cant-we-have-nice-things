<?php

namespace History\Entities\Models\Threads;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Traits\HasSlugTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thread extends AbstractModel
{
    use HasSlugTrait;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->oldest();
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rootComments(): HasMany
    {
        return $this->comments()->whereNull('comment_id');
    }
}
