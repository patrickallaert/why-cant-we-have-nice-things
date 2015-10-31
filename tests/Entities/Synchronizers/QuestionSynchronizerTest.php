<?php
namespace History\Entities\Synchronizers;

use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\TestCase;

class QuestionSynchronizerTest extends TestCase
{
    public function testCanSynchronizerQuestion()
    {
        $request = Request::seed();
        $sync    = new QuestionSynchronizer([
            'name'    => 'lol',
            'choices' => ['yes', 'no'],
        ], $request);

        $question = $sync->synchronize();
        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals([
            'name'       => 'lol',
            'choices'    => ['yes', 'no'],
            'request_id' => $request->id,
        ], $question->toArray());
    }

    public function testDoesntDuplicateQuestions()
    {
        $request = Request::seed();
        $sync    = new QuestionSynchronizer([
            'name'    => 'lol',
            'choices' => ['Yes', 'No'],
        ], $request);

        $first = $sync->persist();
        $this->assertEquals(1, $request->questions()->count());

        $second = $sync->persist();
        $this->assertEquals(1, $request->questions()->count());
        $this->assertEquals($first->id, $second->id);
    }
}
