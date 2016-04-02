<?php
namespace History\CommandBus\Commands;

use DateTime;
use History\Entities\Models\Company;
use History\Entities\Models\User;
use History\Entities\Synchronizers\CompanySynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Services\Github\Github;
use Illuminate\Support\Fluent;

class FetchMetadataHandler extends AbstractHandler
{
    /**
     * @var Github
     */
    protected $github;

    /**
     * @param Github $github
     */
    public function __construct(Github $github)
    {
        $this->github = $github;
    }

    /**
     * @param FetchMetadataCommand $command
     *
     * @return User|null
     */
    public function handle(FetchMetadataCommand $command)
    {
        $user = $command->user;
        if (!$this->shouldUpdateUser($user)) {
            return;
        }

        $search = $this->github->searchUser($user);
        if (!$search || !$search['total_count']) {
            return;
        }

        $githubLogin = $search['items'][0]['login'];
        $synchronizer = $this->updateUserwithInformations($user, $githubLogin);

        // Save Github ID for later use
        /** @var User $user */
        $user = $synchronizer->persist();
        $user->github_id = $githubLogin;
        $user->refreshed_at = new DateTime();
        $user->save();

        return $user;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    protected function shouldUpdateUser(User $user): bool
    {
        $hasMissingInformations = !$user->github_id || !$user->github_avatar;
        $hasntBeenUpdatedRecently = $user->refreshed_at && $user->refreshed_at->diffInDays() > 30;

        return $hasMissingInformations || $hasntBeenUpdatedRecently;
    }

    /**
     * @param User   $user
     * @param string $githubLogin
     *
     * @return UserSynchronizer
     */
    protected function updateUserwithInformations(User $user, $githubLogin): UserSynchronizer
    {
        $informations = $this->github->getUserInformations($githubLogin);
        $informations = new Fluent($informations);

        $company = new Company();
        if ($informations->company) {
            $company = new CompanySynchronizer([
                'name' => $informations->company,
            ]);

            $company = $company->persist();
        }

        $synchronizer = new UserSynchronizer([
            'id' => $user->id,
            'name' => $user->name ?: $informations->login,
            'email' => $user->email ?: $informations->email,
            'full_name' => $user->full_name ?: $informations->full_name,
            'github_avatar' => $informations->avatar_url,
        ], $company);

        return $synchronizer;
    }
}
