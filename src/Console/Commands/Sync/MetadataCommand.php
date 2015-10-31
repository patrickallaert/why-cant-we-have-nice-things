<?php
namespace History\Console\Commands\Sync;

use Exception;
use History\Console\Commands\AbstractCommand;
use History\Entities\Models\User;
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
        $users = User::whereNull('company')->orWhereNull('full_name')->orWhereNull('name')->orWhereNull('email')->get();
        $this->output->progressIterator($users, function (User $user) {
            try {
                $search = $this->github->searchUser($user);
            } catch (Exception $exception) {
                $this->output->writeln('<error>API limit reached</error>');

                return false;
            }

            // If we have results, find informations about
            // the first returned user
            if ($search && $search['total_count']) {
                $githubLogin  = $search['items'][0]['login'];
                $informations = $this->github->getUserInformations($githubLogin);
                $informations = new Fluent($informations);
                $synchronizer = new UserSynchronizer([
                    'id'            => $user->id,
                    'name'          => $user->name ?: $informations->login,
                    'email'         => $user->email ?: $informations->email,
                    'company'       => $user->company ?: $informations->company,
                    'full_name'     => $user->full_name ?: $informations->full_name,
                    'github_avatar' => $informations->avatar_url,
                ]);

                // Save Github ID for later use
                $user            = $synchronizer->persist();
                $user->github_id = $githubLogin;
                $user->save();
            }
        });
    }
}
