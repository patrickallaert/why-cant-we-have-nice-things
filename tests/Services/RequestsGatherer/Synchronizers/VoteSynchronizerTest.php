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

        $question     = new Question();
        $question->id = 1;

        $time = new DateTime();
        $sync = new VoteSynchronizer([
            'choice'     => 2,
            'created_at' => $time,
        ], $question, $user);

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

    public function testDoesntOverwriteExistingVotes()
    {
        $existing = Vote::seed();

        $user     = new User();
        $user->id = $existing->user_id;

        $question     = new Question();
        $question->id = $existing->question_id;

        $time = new DateTime();
        $sync = new VoteSynchronizer([
            'choice'     => 2,
            'created_at' => $time,
        ], $question, $user);

        $vote = $sync->persist();
        $this->assertEquals($vote->id, $existing->id);
        $this->assertEquals(2, $vote->choice);
    }
}
