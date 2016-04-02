<?php

namespace History\Services\RequestsGatherer\Extractors;

use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class VoteExtractorTest extends TestCase
{
    /**
     * @dataProvider provideVotes
     */
    public function testCanExtractVote($user, $choice, $html)
    {
        $extractor = new VoteExtractor(new Crawler($html));
        $vote = $extractor->extract();

        $this->assertEquals($user, $vote['user_id']);
        $this->assertEquals($choice, $vote['choice']);
    }

    /**
     * @return array
     */
    public function provideVotes()
    {
        return [
          ['foobar', 2, <<<'HTML'
<tr>
    <td class="rightalign">
        <a>foobar</a>
    </td>
    <td class="centeralign"></td>
    <td class="centeralign">
        <img src="/lib/images/success.png" title="2015/10/22 22:30">
    </td>
</tr>
HTML
],
            ['foobar', 0, <<<'HTML'
<tr>
    <td class="rightalign">
        <a>foobar</a>
    </td>
    <td class="centeralign"></td>
    <td class="centeralign"></td>
</tr>
HTML
],
        ];
    }
}
