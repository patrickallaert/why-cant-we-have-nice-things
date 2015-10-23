<?php
namespace History\Entities;

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
