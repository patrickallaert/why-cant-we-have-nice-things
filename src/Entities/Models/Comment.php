<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasEvents;
use League\CommonMark\CommonMarkConverter;
use LogicException;

/**
 * @property string name
 * @property string contents
 * @property string xref
 */
class Comment extends AbstractModel
{
    use HasEvents;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['user', 'children'];

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
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
    public function children()
    {
        return $this->hasMany(self::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function getParsedContentsAttribute()
    {
        try {
            return (new CommonMarkConverter())->convertToHtml($this->contents);
        } catch (LogicException $exception) {
            return $this->contents;
        }
    }
}
