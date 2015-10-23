<?php
namespace History\Entities;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
