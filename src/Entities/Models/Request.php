<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasVotes;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasVotes;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
        'approval',
    ];

    public function computeStatistics()
    {
        $this->update([
            'approval' => $this->getApproval(),
        ]);
    }

    /**
     * Did an RFC pass?
     *
     * @return bool
     */
    public function getPassedAttribute()
    {
        return $this->approval > (2 / 3);
    }
}
