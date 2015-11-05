<?php
namespace History\Entities\Observers;

use History\Entities\Models\Vote;
use History\TestCase;

class VoteObserverTest extends TestCase
{
    public function testCanUpdateVoteEventAfterwards()
    {
        $vote = Vote::seed(['choice' => 1]);

        $this->assertTrue($vote->isPositive());
        $this->assertEquals('vote_up', $vote->events()->first()->type);

        $vote->choice = 2;
        $vote->save();

        $this->assertFalse($vote->isPositive());
        $this->assertEquals('vote_down', $vote->events()->first()->type);
    }
}
