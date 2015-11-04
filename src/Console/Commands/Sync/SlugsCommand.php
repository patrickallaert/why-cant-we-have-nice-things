<?php

namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Entities\Models\Company;
use History\Entities\Models\Request;
use History\Entities\Models\User;

class SlugsCommand extends AbstractCommand
{
    /**
     * Run the command.
     */
    public function run()
    {
        $entries = User::all();
        $this->output->progressIterator($entries, function (User $user) {
            $user->slug = $user->getSlug();
            $user->save();
        });

        $entries = Request::all();
        $this->output->progressIterator($entries, function (Request $request) {
            $request->slug = $request->getSlug();
            $request->save();
        });

        $entries = Company::all();
        $this->output->progressIterator($entries, function (Company $company) {
            $company->slug = $company->getSlug();
            $company->save();
        });
    }
}
