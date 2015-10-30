<?php
namespace History\Entities\Models;

use History\TestCase;

class VoteTest extends TestCase
{
    /**
     * @return array
     */
    public function providePositiveVotesTests()
    {
        return [
            ['Yes', true],
            ['No', false],
            ['PHP 4.5', true],
            ['Do not reserve', false],
            ['Keep original implementation', false],
            ["Don't integrate stuff", false],
            ['None', false],
            ['Allow synonyms', true],
            ['No woman no cry', false],
            ['Nonobstant la congolexicomatisation des lois du marchés', true],
        ];
    }

    public function testCanGetTextualAnswer()
    {
        $vote           = new Vote(['choice' => 1]);
        $vote->question = new Question(['choices' => ['yes']]);
        $this->assertEquals('Yes', $vote->answer);

        $vote           = new Vote(['choice' => 1]);
        $vote->question = null;
        $this->assertEquals(1, $vote->answer);
    }

    /**
     * @dataProvider providePositiveVotesTests
     *
     * @param string $text
     * @param bool   $positive
     */
    public function testCanFindPositiveVote($text, $positive)
    {
        $vote           = new Vote(['choice' => 1]);
        $vote->question = new Question(['choices' => [$text]]);
        $this->assertEquals($positive, $vote->isPositive());
    }
}
