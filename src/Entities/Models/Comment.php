<?php
namespace History\Entities\Models;

use League\CommonMark\CommonMarkConverter;

class Comment extends AbstractModel
{
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
