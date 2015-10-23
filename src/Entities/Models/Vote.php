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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
