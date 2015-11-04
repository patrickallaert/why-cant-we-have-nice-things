<?php

namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Question;
use History\Entities\Models\Request;

class QuestionSynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = Question::class;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $timestamps = false;

    /**
     * @param array   $informations
     * @param Request $request
     */
    public function __construct(array $informations, Request $request)
    {
        parent::__construct($informations);

        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchers()
    {
        return [
            [
                'name'       => $this->informations->get('name'),
                'request_id' => $this->request->id,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSynchronizedFields(AbstractModel $entity)
    {
        return [
            'name'       => $this->informations->get('name'),
            'choices'    => $this->informations->get('choices'),
            'request_id' => $this->request->id,
        ];
    }
}
