<?php
namespace History\Entities\Models;

use History\Entities\Traits\CanPass;
use History\Entities\Traits\HasVotes;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use CanPass;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'condition',
        'link',
        'approval',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Compute the RFC's statistics
     */
    public function computeStatistics()
    {
        $approvals = $this->questions->map(function (Question $question) {
            return $question->getApproval();
        })->all();

        $approval = $approvals ? array_sum($approvals) / count($approvals) : 0;

        $this->update([
            'approval' => $approval,
        ]);
    }
}
