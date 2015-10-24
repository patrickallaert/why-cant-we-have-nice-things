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

        // Try to retrieve user if he's already an author
        $user = $email ? User::firstOrNew(['email' => $email]) : new User();
        $user = $user->id || !$username ? $user : User::firstOrNew(['name' => $username]);

        // Fill-in informations
        $user->name      = $username;
        $user->full_name = $this->get('full_name');
        $user->email     = $email;

        return $user;
    }
}
