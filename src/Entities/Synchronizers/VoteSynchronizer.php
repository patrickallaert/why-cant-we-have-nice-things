<?php

namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Question;
use History\Entities\Models\User;
use History\Entities\Models\Vote;

class VoteSynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = Vote::class;

    /**
     * @var Question
     */
    protected $question;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param array    $informations
     * @param Question $question
     * @param User     $user
     */
    public function __construct(array $informations, Question $question, User $user)
    {
        parent::__construct($informations);

        $this->question = $question;
        $this->user     = $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchers()
    {
        return [
            [
                'question_id' => $this->question->id,
                'user_id'     => $this->user->id,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSynchronizedFields(AbstractModel $entity)
    {
        return [
            'choice'      => (int) $this->informations->get('choice'),
            'question_id' => $this->question->id,
            'user_id'     => $this->user->id,
        ];
    }
}
