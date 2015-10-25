<?php
namespace History\Entities\Models;

class Comment extends AbstractModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'contents',
        'xref',
    ];
}
