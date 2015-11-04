<?php

namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Request;

class RequestSynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = Request::class;

    /**
     * {@inheritdoc}
     */
    protected function getMatchers()
    {
        return [
            [
                'link' => $this->informations->get('link'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSynchronizedFields(AbstractModel $entity)
    {
        return [
            'name'         => $this->informations->get('name'),
            'link'         => $this->informations->get('link'),
            'pull_request' => $this->informations->get('pull_request'),
            'contents'     => $this->informations->get('contents'),
            'condition'    => $this->informations->get('condition'),
            'status'       => $this->informations->get('status'),
        ];
    }
}
