<?php
namespace History\Entities\Models;

class Event extends AbstractModel
{
    /**
     * @var array
     */
    const TYPES = [
        'comment_created',
        'rfc_created',
        'rfc_status',
        'rfc_version',
        'vote_down',
        'vote_up',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'metadata',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function eventable()
    {
        return $this->morphTo();
    }
}
