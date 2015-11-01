<?php
namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Entities\Models\User;

class SlugsCommand extends AbstractCommand
{
    /**
     * Run the command.
     */
    public function run()
    {
        $users = User::all();
        $this->output->progressIterator($users, function (User $user) {
            $user->slug = $user->getSlug();
            $user->save();
        });
    }
}
