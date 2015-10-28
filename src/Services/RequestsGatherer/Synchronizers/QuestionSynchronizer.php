<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Services\RequestsGatherer\AbstractModel;

class QuestionSynchronizer extends AbstractSynchronizer
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @param array   $informations
     * @param Request $request
     */
    public function __construct(array $informations, Request $request)
    {
        $this->informations = $informations;
        $this->request      = $request;
    }

    /**
     * Synchronize an user with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize()
    {
        $question = Question::firstOrNew([
            'name'       => $this->get('name'),
            'choices'    => json_encode($this->get('choices')),
            'request_id' => $this->request->id,
        ]);

        $question->choices = $this->get('choices');
        $question->request_id = $this->request->id;

        return $question;
    }
}
