<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasEvents;
use League\CommonMark\CommonMarkConverter;

class Comment extends AbstractModel
{
    use HasEvents;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'contents',
        'xref',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
    public function children()
    {
        return $this->hasMany(Comment::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function getParsedContentsAttribute()
    {
        return (new CommonMarkConverter())->convertToHtml($this->contents);
    }
}
