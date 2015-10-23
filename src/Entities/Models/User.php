<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasVotes;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasVotes;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}
