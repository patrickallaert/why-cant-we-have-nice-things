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

    public function testCanComputeQuestionStatistics()
    {
        $question        = new Question(['choices' => 2]);
        $question->votes = new Collection([
            new Vote(['choice' => 2]),
            new Vote(['choice' => 1]),
            new Vote(['choice' => 1]),
        ]);

        $stats = $this->computer->forQuestion($question);
        $this->assertEquals([
            'approval' => 2 / 3,
            'passed'   => true,
        ], $stats);
    }

    public function testCanComputeQuestionStatsWithoutVotes()
    {
        $question        = new Question(['choices' => 2]);
        $question->votes = new Collection([]);

        $stats = $this->computer->forQuestion($question);
        $this->assertEquals([
            'approval' => 0,
            'passed'   => false,
        ], $stats);
    }

    public function testCanCheckIfHasPassed()
    {
        $request = new Request(['condition' => 'Must be 50%+1']);
        $this->assertTrue($this->computer->hasPassed($request, 0.6));
        $this->assertFalse($this->computer->hasPassed($request, 0.4));

        $request = new Request(['condition' => 'Must be 2/3']);
        $this->assertTrue($this->computer->hasPassed($request, 0.8));
        $this->assertFalse($this->computer->hasPassed($request, 0.4));
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
            'passed'   => true,
        ], $stats);
    }

    public function testCanComputeQuestionApprovalIfNotDefined()
    {
        $question        = new Question(['choices' => 2]);
        $question->votes = new Collection([
            new Vote(['choice' => 1]),
        ]);

        $request            = new Request();
        $request->questions = new Collection([$question]);

        $stats = $this->computer->forRequest($request);
        $this->assertEquals([
            'approval' => 1,
            'passed'   => true,
        ], $stats);
    }

    public function testCanComputeUserStatistics()
    {
        $question        = new Question(['choices' => 2]);
        $question->votes = new Collection([
           new Vote(['choice'  => 1]),
            new Vote(['choice' => 2]),
            new Vote(['choice' => 2]),
        ]);

        $user        = new User([]);
        $user->votes = new Collection([
            (new Vote(['choice' => 1]))->setAttribute('question', $question),
            (new Vote(['choice' => 2]))->setAttribute('question', $question),
            (new Vote(['choice' => 2]))->setAttribute('question', $question),
        ]);

        $stats = $this->computer->forUser($user);
        $this->assertEquals([
            'yes_votes'   => 1,
            'no_votes'    => 2,
            'total_votes' => 3,
            'approval'    => 1 / 3,
            'hivemind'    => 2 / 3,
        ], $stats);
    }
}
