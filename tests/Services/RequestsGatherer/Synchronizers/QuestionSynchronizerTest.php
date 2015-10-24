<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\TestCase;

class QuestionSynchronizerTest extends TestCase
{
    public function testCanSynchronizerQuestion()
    {
        $request     = new Request();
        $request->id = 1;

        $sync = new QuestionSynchronizer([
            'name'    => 'lol',
            'choices' => 2,
        ], $request);

        $question = $sync->synchronize();
        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals([
            'name'       => 'lol',
            'choices'    => 2,
            'request_id' => 1,
        ], $question->toArray());
    }
}
