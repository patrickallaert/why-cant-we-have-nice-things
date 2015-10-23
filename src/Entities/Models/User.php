<?php
namespace History\Entities\Models;

use History\Entities\Traits\HasVotes;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasVotes;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'yes_votes',
        'no_votes',
        'total_votes',
        'approval',
        'hivemind',
    ];

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ATTRIBUTES /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Recompute the user statistics.
     */
    public function computeStatistics()
    {
        $this->update([
            'yes_votes'   => $this->getYesVotes()->count(),
            'no_votes'    => $this->getNoVotes()->count(),
            'total_votes' => $this->votes->count(),
            'approval'    => $this->getApproval(),
            'hivemind'    => $this->getHivemind(),
        ]);
    }
}
