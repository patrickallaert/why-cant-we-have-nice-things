<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Entities\Models\User;

class UserSynchronizer extends AbstractSynchronizer
{
    /**
     * @return User
     */
    public function synchronize()
    {
        $email    = $this->get('email');
        $username = $this->get('username');
        $fullName = $this->get('full_name');

        $components = [
            ['name', $username],
            ['email', $email],
            ['email', preg_replace('/@(.+)/', '@php.net', $email)],
            ['full_name', $fullName],
        ];

        // If we have no username but have an email
        // try to infere username from it
        if (!$username && $email) {
            $username     = explode('@', $email)[0];
            $components[] = ['name', $username];
        }

        // Try to retrieve user if he's already an author
        $user = new User();
        foreach ($components as list($key, $value)) {
            if (!$value) {
                continue;
            }

            $user = $user = User::firstOrNew([$key => $value]);
            if ($user->exists) {
                break;
            }
        }

        // Do not replace the user's email if it is more valid than the one we have or
        // if we don't even have one
        $shouldReplaceEmail = $email && (!$user->email || strpos($user->email, '@php.net'));
        $email              = $shouldReplaceEmail ? $email : $user->email;

        // If the user has an email in @zend.com we can
        // probably assume he works at Zend
        $company = strpos($email, '@zend') !== false ? 'Zend' : $this->get('company');

        // Fill-in informations
        $user->name          = $user->name ?: $username;
        $user->full_name     = $user->full_name ?: $fullName;
        $user->company       = $user->company ?: $company;
        $user->email         = $email;
        $user->contributions = $user->contributions ?: ($this->get('contributions') ?: []);

        return $user;
    }
}
