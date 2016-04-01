<?php

namespace History\Entities\Synchronizers;

use DateTime;
use History\Entities\Models\Question;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\TestCase;

class VoteSynchronizerTest extends TestCase
{
    public function testCanSynchronizeVote()
    {
        $user = User::seed();
        $question = Question::seed();

        $time = new DateTime();
        $sync = new VoteSynchronizer([
            'choice' => 2,
            'timestamps' => $time,
        ], $question, $user);

        $vote = $sync->synchronize();
        $this->assertInstanceOf(Vote::class, $vote);
        $this->assertEquals([
            'choice' => 2,
            'question_id' => $question->id,
            'user_id' => $user->id,
            'created_at' => $time->format('Y-m-d H:i:s'),
            'updated_at' => $time->format('Y-m-d H:i:s'),
        ], $vote->toArray());
    }

    public function testDoesntOverwriteExistingVotes()
    {
        Vote::truncate();
        $existing = Vote::seed();

        $time = new DateTime();
        $sync = new VoteSynchronizer([
            'choice' => 2,
            'timestamps' => $time,
        ], $existing->question, $existing->user);

        $vote = $sync->persist();
        $this->assertEquals($existing->id, $vote->id);
        $this->assertEquals(2, $vote->choice);
    }
}
