<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Entities\Models\Question;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\Services\RequestsGatherer\AbstractModel;

class VoteSynchronizer extends AbstractSynchronizer
{
    /**
     * @var Question
     */
    protected $question;
    /**
     * @var User
     */
    private $user;

    /**
     * @param array    $informations
     * @param Question $question
     * @param User     $user
     */
    public function __construct(array $informations, Question $question, User $user)
    {
        $this->informations = $informations;
        $this->question     = $question;
        $this->user         = $user;
    }

    /**
     * Synchronize an user with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize()
    {
        $vote = new Vote(['choice' => $this->get('choice')]);

        $vote->question_id = $this->question->id;
        $vote->user_id     = $this->user->id;
        $vote->created_at  = $this->get('created_at');
        $vote->updated_at  = $this->get('created_at');

        return $vote;
    }
}
