<?php
namespace History\Entities\Models;

class Event extends AbstractModel
{
    /**
     * @var array
     */
    const TYPES = ['vote_up', 'vote_down', 'rfc_created', 'rfc_status'];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function eventable()
    {
        return $this->morphTo();
    }
}
