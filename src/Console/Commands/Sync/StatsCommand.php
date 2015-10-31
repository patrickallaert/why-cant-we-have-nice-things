<?php
namespace History\Console\Commands\Sync;

use History\Console\Commands\AbstractCommand;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\StatisticsComputer\StatisticsComputer;

class StatsCommand extends AbstractCommand
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
        $this->output->title('Refreshing statistics');

        // Fetch the entities to refresh
        $users     = User::with('votes.question.votes', 'requests')->get();
        $questions = Question::with('votes')->get();
        $requests  = Request::with('questions.votes')->get();

        $this->output->section('Refreshing User statistics');
        $this->output->progressIterator($users, function (User $user) {
            $user->fill($this->computer->forUser($user))->saveIfDirty();
        });

        $this->output->section('Refreshing Question statistics');
        $this->output->progressIterator($questions, function (Question $question) {
            $question->fill($this->computer->forQuestion($question))->saveIfDirty();
        });

        $this->output->section('Refreshing Request statistics');
        $this->output->progressIterator($requests, function (Request $request) {
            $request->fill($this->computer->forRequest($request))->saveIfDirty();
        });
    }
}
