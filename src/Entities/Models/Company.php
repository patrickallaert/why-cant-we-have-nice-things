<?php
namespace History\Entities\Models;

/**
 * @property string name
 */
class Company extends AbstractModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
