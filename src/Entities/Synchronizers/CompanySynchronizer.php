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
     * {@inheritdoc}
     */
    protected function sanitize(array $informations)
    {
        $informations       = parent::sanitize($informations);
        $informations->name = strtr($informations->name, [
            ' - The PHP Consulting Company' => '',
        ]);

        return $informations;
    }

    /**
     * {@inheritdoc}
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
