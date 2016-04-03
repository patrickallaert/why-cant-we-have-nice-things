<?php

namespace History\Entities\Models\Threads;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\User;
use History\Entities\Traits\HasEventsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use League\CommonMark\CommonMarkConverter;
use LogicException;

/**
 * @property string name
 * @property string contents
 * @property string parsed_contents
 * @property string xref
 */
class Comment extends AbstractModel
{
    use HasEventsTrait;

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class);
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param CommonMarkConverter|null $converter
     *
     * @return string
     */
    public function getParsedContentsAttribute(CommonMarkConverter $converter = null)
    {
        $converter = $converter ?: new CommonMarkConverter();

        try {
            return $converter->convertToHtml($this->contents);
        } catch (LogicException $exception) {
            return $this->contents;
        }
    }
}
