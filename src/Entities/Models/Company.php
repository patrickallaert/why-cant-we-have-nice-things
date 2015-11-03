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
}
