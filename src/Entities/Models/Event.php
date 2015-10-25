<?php
namespace History\Entities\Models;

class Event extends AbstractModel
{
    /**
     * @var array
     */
    const TYPES = ['vote', 'rfc_created', 'rfc_closed'];

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'created_at',
        'updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function eventable()
    {
        return $this->morphTo();
    }
}
