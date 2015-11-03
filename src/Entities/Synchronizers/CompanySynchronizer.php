<?php
namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Company;

class CompanySynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = Company::class;

    /**
     * Get the criterias against which an existing
     * entity will be matched.
     *
     * @return array
     */
    protected function getMatchers()
    {
        return [
            ['name' => $this->informations->get('name')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSynchronizedFields(AbstractModel $entity)
    {
        return [
            'name' => $this->informations->get('name'),
        ];
    }
}
