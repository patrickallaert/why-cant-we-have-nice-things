<?php

namespace History\Entities\Traits;

use History\Entities\Models\Vote;
use History\TestCase;

class HasEventsTest extends TestCase
{
    public function testCanFireEvents()
    {
        $vote = Vote::seed();
        $vote->registerEvent('vote_up');

        $events = $vote->events->toArray();
        $this->assertEquals([
            'id'             => $events[1]['id'],
            'type'           => 'vote_up',
            'eventable_id'   => $vote->id,
            'eventable_type' => 'History\\Entities\\Models\\Vote',
            'metadata'       => [],
            'created_at'     => $events[1]['created_at'],
            'updated_at'     => $events[1]['updated_at'],
        ], $events[1]);
    }
}
