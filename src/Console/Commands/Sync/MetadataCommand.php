<?php
namespace History\Console\Commands\Sync;

use Exception;
use History\Console\Commands\AbstractCommand;
use History\Entities\Models\Company;
use History\Entities\Models\User;
use History\Entities\Synchronizers\CompanySynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Services\Github\Github;
use Illuminate\Support\Fluent;

class MetadataCommand extends AbstractCommand
{
    /**
     * @var Github
     */
    protected $github;

    /**
     * MetadataCommand constructor.
     *
     * @param Github $github
     */
    public function __construct(Github $github)
    {
        $this->github = $github;
    }

    /**
     * Run the command.
     */
    protected function run()
    {
        $this->output->title('Refreshing metadata');

        // Only get users with holes in their informations
        $users = User::whereNull('github_id')->get();
        $this->output->progressIterator($users, function (User $user) {
            try {
                $search = $this->github->searchUser($user);
            } catch (Exception $exception) {
                $this->output->writeln('<error>API limit reached</error>');

                return false;
            }

            if ($search && $search['total_count']) {
                $githubLogin = $search['items'][0]['login'];
                $synchronizer = $this->updateUserwithInformations($user, $githubLogin);

                // Save Github ID for later use
                /** @var User $user */
                $user = $synchronizer->persist();
                $user->github_id = $githubLogin;
                $user->save();
            }
        });
    }

    /**
     * @param User   $user
     * @param string $githubLogin
     *
     * @return UserSynchronizer
     */
    public function updateUserwithInformations(User $user, $githubLogin)
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
            'id'            => $user->id,
            'name'          => $user->name ?: $informations->login,
            'email'         => $user->email ?: $informations->email,
            'full_name'     => $user->full_name ?: $informations->full_name,
            'github_avatar' => $informations->avatar_url,
        ], $company);

        return $synchronizer;
    }
}
