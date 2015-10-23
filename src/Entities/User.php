<?php
namespace History\Entities;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
    ];
}
