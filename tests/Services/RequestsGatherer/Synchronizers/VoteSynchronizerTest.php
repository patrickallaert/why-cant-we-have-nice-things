<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use DateTime;
use History\Entities\Models\Question;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\TestCase;

class VoteSynchronizerTest extends TestCase
{
    public function testCanSynchronizeVote()
    {
        $user     = new User();
        $user->id = 1;

        $vote     = new Question();
        $vote->id = 1;

        $time = new DateTime();
        $sync = new VoteSynchronizer([
            'choice'     => 2,
            'created_at' => $time,
        ], $vote, $user);

        $vote = $sync->synchronize();
        $this->assertInstanceOf(Vote::class, $vote);
        $this->assertEquals([
            'choice'      => 2,
            'question_id' => 1,
            'user_id'     => 1,
            'created_at'  => $time->format('Y-m-d H:i:s'),
            'updated_at'  => $time->format('Y-m-d H:i:s'),
        ], $vote->toArray());
    }
}
