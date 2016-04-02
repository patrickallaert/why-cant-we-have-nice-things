<?php

namespace History\Entities\Traits;

use History\Entities\Models\Question;
use History\Entities\Models\Vote;
use History\TestCase;

class HasEventsTest extends TestCase
{
    public function testCanFireEvents()
    {
        $question = Question::seed();
        $vote = Vote::seed(['question_id' => $question->id]);
        $vote->events()->delete();
        $vote->registerEvent('vote_up');

        $events = $vote->events()->get()->toArray();
        $this->assertCount(1, $events);
        $this->assertEquals([
            'id' => $events[0]['id'],
            'type' => 'vote_up',
            'eventable_id' => $vote->id,
            'eventable_type' => 'History\\Entities\\Models\\Vote',
            'metadata' => [],
            'created_at' => $events[0]['created_at'],
            'updated_at' => $events[0]['updated_at'],
        ], $events[0]);
    }
}
