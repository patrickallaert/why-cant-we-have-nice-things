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
        foreach ($this->output->progressIterator($entries) as $user) {
            $user->slug = $user->getSlug();
            $user->save();
        };

        $entries = Request::all();
        foreach ($this->output->progressIterator($entries) as $request) {
            $request->slug = $request->getSlug();
            $request->save();
        };

        $entries = Company::all();
        foreach ($this->output->progressIterator($entries) as $company) {
            $company->slug = $company->getSlug();
            $company->save();
        };
    }
}
