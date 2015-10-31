<?php
namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\StatisticsComputer\StatisticsComputer;

class SyncStatsCommand extends AbstractCommand
{
    /**
     * @var StatisticsComputer
     */
    protected $computer;

    /**
     * @param StatisticsComputer $computer
     */
    public function __construct(StatisticsComputer $computer)
    {
        $this->computer = $computer;
    }

    /**
     * Run the command.
     */
    public function run()
    {
        $users     = User::with('votes.question.votes', 'requests')->get();
        $questions = Question::with('votes')->get();
        $requests  = Request::with('questions.votes')->get();

        $this->comment('Refreshing User statistics');
        $this->progressIterator($users, function (User $user) {
            $user->fill($this->computer->forUser($user))->saveIfDirty();
        });

        $this->comment('Refreshing Question statistics');
        $this->progressIterator($questions, function (Question $question) {
            $question->fill($this->computer->forQuestion($question))->saveIfDirty();
        });

        $this->comment('Refreshing Request statistics');
        $this->progressIterator($requests, function (Request $request) {
            $request->fill($this->computer->forRequest($request))->saveIfDirty();
        });
    }
}
