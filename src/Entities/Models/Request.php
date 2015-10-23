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
        'condition',
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
        $majority = 0.5;
        if (strpos($this->condition, '2/3') !== false) {
            $majority = 2 / 3;
        }

        return $this->approval > $majority;
    }
}
