<?php
namespace History\Entities\Models;

use History\Entities\Traits\CanPass;
use History\Entities\Traits\HasVotes;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasVotes;
    use CanPass;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'approval',
        'request_id',
    ];

    /**
     * Compute the question's statistics
     */
    public function computeStatistics()
    {
        $this->update([
           'approval' => $this->getApproval(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
