<?php

namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Version;

class VersionSynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = Version::class;

    /**
     * {@inheritdoc}
     */
    protected function getMatchers()
    {
        return [
            [
                'version'    => $this->informations->get('version'),
                'request_id' => $this->informations->get('request_id'),
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
            'version'    => $this->informations->get('version'),
            'request_id' => $this->informations->get('request_id'),
        ];
    }
}
