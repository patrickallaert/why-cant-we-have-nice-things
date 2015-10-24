<?php
namespace History\Services\StatisticsComputer;

use History\Collection;
use History\Entities\Models\Question;
use History\Entities\Models\Vote;
use History\TestCase;

class StatisticsComputerTest extends TestCase
{
    public function testCanComputeQuestionStatistics()
    {
        $question        = new Question(['choices' => 2]);
        $question->votes = new Collection([
            new Vote(['choice' => 2]),
            new Vote(['choice' => 1]),
            new Vote(['choice' => 1]),
        ]);

        $stats = (new StatisticsComputer())->forQuestion($question);
        $this->assertEquals([
            'approval' => 2 / 3,
            'passed'   => true,
        ], $stats);
    }

    public function testCanComputeQuestionStatsWithoutVotes()
    {
        $question        = new Question(['choices' => 2]);
        $question->votes = new Collection([]);

        $stats = (new StatisticsComputer())->forQuestion($question);
        $this->assertEquals([
            'approval' => 0,
            'passed'   => false,
        ], $stats);
    }
}
