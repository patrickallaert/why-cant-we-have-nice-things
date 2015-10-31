<?php
namespace History\Services\StatisticsComputer;

use History\Collection;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\TestCase;

class StatisticsComputerTest extends TestCase
{
    /**
     * @var StatisticsComputer
     */
    protected $computer;

    public function setUp()
    {
        parent::setUp();

        $this->computer = new StatisticsComputer();
    }

    /**
     * @dataProvider provideConditions
     */
    public function testCanComputeDependingOnMajorityConditions($condition, $passed)
    {
        $question          = Question::seed();
        $question->request = new Request(['condition' => $condition]);
        Vote::seed(['choice' => 2, 'question_id' => $question->id]);
        Vote::seed(['choice' => 1, 'question_id' => $question->id]);
        Vote::seed(['choice' => 1, 'question_id' => $question->id]);

        $stats = $this->computer->forQuestion($question);
        $this->assertEquals([
            'approval' => round(2 / 3, 6),
            'passed'   => $passed,
        ], $stats);
    }

    public function testCanComputeQuestionStatsWithoutVotes()
    {
        $question        = Question::seed();
        $question->votes = new Collection([]);

        $stats = $this->computer->forQuestion($question);
        $this->assertEquals([
            'approval' => 0,
            'passed'   => false,
        ], $stats);
    }

    public function testCanComputeStatisticsForRequest()
    {
        $request            = new Request();
        $request->questions = new Collection([
            new Question(['approval' => 1]),
            new Question(['approval' => 0.5]),
        ]);

        $stats = $this->computer->forRequest($request);
        $this->assertEquals([
            'approval' => 0.75,
        ], $stats);
    }

    public function testCanComputeQuestionApprovalIfNotDefined()
    {
        $question = Question::seed();
        Vote::seed(['choice' => 1, 'question_id' => $question->id]);

        $request            = new Request();
        $request->questions = new Collection([$question]);

        $stats = $this->computer->forRequest($request);
        $this->assertEquals([
            'approval' => 1,
        ], $stats);
    }

    public function testCanComputeUserStatistics()
    {
        $question = Question::seed();
        Vote::seed(['choice' => 1, 'question_id' => $question->id]);
        Vote::seed(['choice' => 2, 'question_id' => $question->id]);
        Vote::seed(['choice' => 2, 'question_id' => $question->id]);

        $user        = new User([]);
        $user->votes = new Collection([
            (new Vote(['choice' => 1]))->setAttribute('question', $question),
            (new Vote(['choice' => 2]))->setAttribute('question', $question),
            (new Vote(['choice' => 2]))->setAttribute('question', $question),
        ]);
        $user->requests = new Collection([
            new Request(['status' => 4]),
            new Request(['status' => 4]),
        ]);
        $user->approvedRequests = new Collection([
            new Request(['status' => 4]),
        ]);

        $stats = $this->computer->forUser($user);
        $this->assertEquals([
            'yes_votes'   => 1,
            'no_votes'    => 2,
            'total_votes' => 3,
            'approval'    => round(1 / 3, 6),
            'success'     => round(1 / 2, 6),
            'hivemind'    => round(2 / 3, 6),
        ], $stats);
    }

    public function testSkipsIfNoData()
    {
        $user        = new User([]);
        $user->votes = new Collection([]);

        $stats = $this->computer->forUser($user);
        $this->assertEquals([
            'yes_votes'   => 0,
            'no_votes'    => 0,
            'total_votes' => 0,
            'approval'    => 0,
            'success'     => 0,
            'hivemind'    => 0,
        ], $stats);
    }

    /**
     * @return array
     */
    public function provideConditions()
    {
        return [
            ['2/3', false],
            ['50%+1', true],
        ];
    }
}
