<?php
namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\User;

class UserSynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = User::class;

    /**
     * @var array
     */
    protected $protected = [
        'name',
        'full_name',
        'company',
        'contributions',
    ];

    /**
     * @var bool
     */
    protected $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected function sanitize(array $informations)
    {
        $informations = parent::sanitize($informations);

        // If the username contains a space, it's most likely a name
        if (!$informations->get('full_name') && strpos($informations->get('name'), ' ') !== false) {
            $informations['full_name'] = $informations->get('name');
            $informations['name']      = null;
        }

        // If the user has an email in @zend.com we can
        // probably assume he works at Zend
        $informations['company'] = strpos($informations->get('email'), '@zend') !== false ? 'Zend' : $informations->get('company');

        return $informations;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchers()
    {
        $email    = $this->informations->get('email');
        $username = $this->informations->get('name');
        $fullName = $this->informations->get('full_name');

        $matchers = [
            ['id' => $this->informations->get('id')],
            ['name'      => $username],
            ['email'     => $email],
            ['email'     => preg_replace('/@(.+)/', '@php.net', $email)],
            ['full_name' => $fullName],
        ];

        // If we have no username but have an email
        // try to infere username from it
        if (!$username && $email) {
            $username   = explode('@', $email)[0];
            $matchers[] = ['name' => $username];
        }

        return $matchers;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSynchronizedFields(AbstractModel $entity)
    {
        // Do not replace the user's email if it is more valid than the one we have or
        // if we don't even have one
        $email              = $this->informations->get('email');
        $shouldReplaceEmail = $email && (!$entity->email || strpos($entity->email, '@php.net'));

        return [
            'name'          => $this->informations->get('name'),
            'email'         => $shouldReplaceEmail ? $email : $entity->email,
            'full_name'     => $this->informations->get('full_name'),
            'contributions' => $this->informations->get('contributions') ?: [],
            'company'       => $this->informations->get('company'),
            'github_avatar' => $this->informations->get('github_avatar'),
        ];
    }
}
