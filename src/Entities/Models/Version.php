<?php

namespace History\Entities\Models;

use History\Entities\Traits\HasEvents;

/**
 * @property string  name
 * @property string  version
 * @property Request request
 */
class Version extends AbstractModel
{
    use HasEvents;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'version',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
