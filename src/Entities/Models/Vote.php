<?php
namespace History\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'request_id',
        'vote',
    ];
}
