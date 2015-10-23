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
     * @return float
     */
    public function computeHivemind()
    {
        $hivemind = [];
        foreach ($this->votes as $vote) {
            $majority   = $vote->request->approval > 0.5;
            $user       = (bool) $vote->vote;
            $hivemind[] = $user === $majority;
        }

        $hivemind = count(array_filter($hivemind)) / count($hivemind);
        $hivemind = round($hivemind, 3);

        return $hivemind;
    }
}
