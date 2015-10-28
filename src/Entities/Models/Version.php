<?php
namespace History\Entities\Models;

class Version extends AbstractModel
{
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
