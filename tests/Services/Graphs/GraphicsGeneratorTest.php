<?php

namespace History\Services\Graphs;

use History\Entities\Models\Question;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\TestCase;

class GraphicsGeneratorTest extends TestCase
{
    public function testCanComputeUserPositiveness()
    {
        $months = [
            '2011-10-10',
            '2012-10-10',
            '2013-10-10',
            '2014-10-10',
            '2015-10-10',
            '2015-11-10',
            '2015-12-10',
            '2016-01-10',
            '2016-02-10',
            '2016-03-10',
            '2016-04-10',
        ];

        // Seed dummy data
        $user = User::seed();
        $question = Question::seed();
        foreach ($months as $key => $month) {
            $choice = $key < 2 ? 1 : 2;
            Vote::seed(['user_id' => $user->id, 'question_id' => $question->id, 'choice' => $choice, 'created_at' => $month]);
        }

        // Compute graph data
        $generator = new GraphicsGenerator();
        $generator->setRound(false);
        $data = $generator->computePositiveness($user);

        $this->assertEquals([
            'labels' => [
                '2011-10',
                '2013-10',
                '2015-10',
                '2015-12',
                '2016-02',
                '2016-04',
            ],
            'datasets' => [
                [
                    'fillColor' => '#33cc73',
                    'strokeColor' => '#279B57',
                    'data' => [
                        1 / 1,
                        2 / 3,
                        2 / 5,
                        2 / 7,
                        2 / 9,
                        2 / 11,
                    ],
                ],
            ],
        ], $data);
    }
}
