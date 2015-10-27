<?php
namespace History\Console\Commands;

use History\Entities\Models\Comment;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;

class SeedCommand extends AbstractCommand
{
    /**
     * Run the command.
     */
    protected function run()
    {
        User::seed(50);
        $requests = Request::seed(50);
        Question::seed(count($requests) * 3);
        Vote::seed(200);
        Comment::seed(200);

        $this->output->writeln('<info>Database seeded</info>');
    }
}
