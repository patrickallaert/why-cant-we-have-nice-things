<?php

namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DateTimeZone;
use History\Entities\Models\Request;
use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class RequestExtractorTest extends TestCase
{
    public function testCanExtractRequest()
    {
        $html         = $this->getDummyPage('rfc');
        $informations = $this->getInformationsFromHtml($html);
        $timezone     = new DateTimeZone('UTC');

        $contents = $informations['contents'];
        unset($informations['contents']);

        $this->assertEquals([
            'name'         => 'Support Class Constant Visibility',
            'status'       => 3,
            'condition'    => '2/3',
            'pull_request' => 'https://github.com/php/php-src/pull/1494',
            'timestamps'   => DateTime::createFromFormat('Y-m-d H:i:s', '2015-09-13 00:00:00'),
            'authors'      => [
                ['full_name' => 'Sean DuBois', 'email' => 'sean@siobud.com'],
                ['full_name' => 'Reeze Xia', 'email' => 'reeze@php.net'],
            ],
            'questions' => [
                [
                    'name'    => 'Class Constant Visibility',
                    'choices' => ['Yes', 'No'],
                    'votes'   => [
                        [
                            'user_id'    => 'ajf',
                            'choice'     => 2,
                            'timestamps' => DateTime::createFromFormat('Y-m-d H:i', '2015-10-22 22:30', $timezone),
                        ],
                        [
                            'user_id'    => 'ajf',
                            'choice'     => 1,
                            'timestamps' => DateTime::createFromFormat('Y-m-d H:i', '2015-10-22 22:30', $timezone),
                        ],
                    ],
                ],
            ],
            'versions' => [
                ['version' => '0.1', 'name' => 'Initial version', 'timestamps' => false],
                ['version' => '0.2', 'name' => 'Adopted by Sean DuBois sean@siobud.com', 'timestamps' => false],
            ],
        ], $informations);

        $this->assertNotContains('<pre class="code php">', $contents);
        $this->assertContains('<pre><code class="php">', $contents);
    }

    public function testCanParseAuthors()
    {
        $html = <<<'HTML'
        Author:
Foo Bar
<a>foo@bar.com</a>
, Bar Foo
<a>&laquo;bar@php.net&raquo;</a>,
Baz Qux, <a>baz@qux.net</a>
HTML;
        $informations = $this->getInformationsFromInformationBlock($html);
        $this->assertEquals([
            ['full_name' => 'Foo Bar', 'email' => 'foo@bar.com'],
            ['full_name' => 'Bar Foo', 'email' => 'bar@php.net'],
            ['full_name' => 'Baz Qux', 'email' => 'baz@qux.net'],
        ], $informations['authors']);

        $html = <<<'HTML'
<strong>Author:</strong> <a href="http://www.porcupine.org/wietse/" class="urlextern" title="http://www.porcupine.org/wietse/" rel="nofollow">Wietse Venema (wietse@porcupine.org)</a> <br>
 IBM T.J. Watson Research Center <br>
 Hawthorne, NY, USA
HTML;
        $informations = $this->getInformationsFromInformationBlock($html);
        $this->assertEquals([
            ['full_name' => 'Wietse Venema', 'email' => 'wietse@porcupine.org'],
        ], $informations['authors']);

        $html         = ' Author: Ryusuke Sekiyama &lt;rsky0711 at gmail . com&gt;, Sebastian Deutsch &lt;sebastian.deutsch at 9elements . com&gt;';
        $informations = $this->getInformationsFromInformationBlock($html);
        $this->assertEquals([
            ['full_name' => 'Ryusuke Sekiyama', 'email' => 'rsky0711@gmail.com'],
            ['full_name' => 'Sebastian Deutsch', 'email' => 'sebastian.deutsch@9elements.com'],
        ], $informations['authors']);
    }

    public function testCanParseAuthorsWithoutEmail()
    {
        $informations = $this->getInformationsFromInformationBlock('Author: Márcio Almada');
        $this->assertEquals([
            ['full_name' => 'Marcio Almada'],
        ], $informations['authors']);
    }

    public function testCanParseConditionsFromProposedVotingChoices()
    {
        $html         = '<div id="proposed_voting_choices"></div><div><p>I like butts</p><p>Requires a 2/3 majority</p></div>';
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals('2/3', $informations['condition']);
    }

    /**
     * @dataProvider provideDates
     *
     * @param string $text
     * @param string $date
     */
    public function testCanParseWeirdAssDateFormats($text, $date)
    {
        $informations = $this->getInformationsFromInformationBlock($text);
        $matcher      = $date instanceof DateTime ? $date : DateTime::createFromFormat('Y-m-d H:i:s', $date);
        $this->assertEquals($matcher->format('Y-m-d'), $informations['timestamps']->format('Y-m-d'));
    }

    public function testCanGetDateFromFooterIfInvalidFormat()
    {
        $html = <<<'HTML'
<div>
    <div class="page group"><div class="level1"><ul><li>Date: FUCK YOU</li></ul></div></div>
    <div class="docInfo"><bdi>rfc/class_const_visibility.txt</bdi> · Last modified: 2015/10/28 16:06 by <bdi>sean-der</bdi></div>
</div>
HTML;
        $informations = $this->getInformationsFromHtml($html);
        $this->assertEquals(DateTime::createFromFormat('Y-m-d H:i:s', '2015-10-28 00:00:00'), $informations['timestamps']);
    }

    /**
     * @dataProvider provideTitles
     *
     * @param string $html
     * @param string $name
     */
    public function testCanCleanupRequestTitle($html, $name)
    {
        $informations = $this->getInformationsFromHtml($html);
        $this->assertEquals($name, $informations['name']);
    }

    /**
     * @dataProvider provideStatuses
     *
     * @param string $text
     * @param int    $status
     */
    public function testCanParseStatus($text, $status)
    {
        $informations = $this->getInformationsFromInformationBlock($text);
        $this->assertEquals($status, $informations['status']);
    }

    public function testIgnoresStatusIfAllPollsAreClosed()
    {
        $html = <<<'HTML'
<div class="page group">
    <div class="level1"><ul><li>Status: i has voting</li></ul></div>

    <form action="">
        <table class="inline">
            <tbody><tr><td colspan="8">POOL IS CLOSED</td></tr></tbody>
        </table>
    </form>

    <form><table class="inline"></table></form>
</div>
HTML;
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals(3, $informations['status']);

        $html = <<<'HTML'
<div class="page group">
    <div class="level1"><ul><li>Status: i has voting</li></ul></div>

    <table class="inline">
        <tbody><tr>Some unrelated table</tr></tbody>
    </table>

    <form>
        <table class="inline">
            <tbody><tr><td colspan="8">POOL IS CLOSED</td></tr></tbody>
        </table>
    </form>

    <form>
        <table class="inline">
            <tbody><tr><td colspan="8">POOL IS CLOSED</td></tr></tbody>
        </table>
    </form>
</div>
HTML;
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals(4, $informations['status']);
    }

    public function testMinifiesHtmlContent()
    {
        $html = <<<'HTML'
<div class="page group">
    <!-- lol -->
    <div class="level1"><ul><li>Status: i has voting</li></ul></div>
<pre>
    foo
    bar
</pre>
</div>
HTML;

        $extractor = $this->getInformationsFromHtml($html);
        $this->assertEquals(<<<'HTML'
<div
class="level1"><ul><li>Status: i has voting</li></ul></div><pre>
    foo
    bar
</pre>
HTML
            , $extractor['contents']);
    }

    public function testCanConvertCodeblocks()
    {
        $html = <<<'HTML'
<div class="page group">
    <pre class="code php">
    $echo 'lol';
    </pre>
</div>
HTML;

        $extractor = $this->getInformationsFromHtml($html);
        $this->assertEquals(<<<'HTML'
<pre><code class="php">
    $echo 'lol';
    </code></pre>
HTML
            , $extractor['contents']);
    }

    public function testAssumesNonHintedCodeblocksArePhp()
    {
        $html = <<<'HTML'
<div class="page group">
    <pre class="code">
    $echo 'lol';
    </pre>
</div>
HTML;

        $extractor = $this->getInformationsFromHtml($html);
        $this->assertEquals(<<<'HTML'
<pre><code class="php">
    $echo 'lol';
    </code></pre>
HTML
            , $extractor['contents']);
    }

    public function testCanStripVotingTablesFromContent()
    {
        $html = <<<'HTML'
<div class="page group">
    <table class="inline">
        <tbody><tr>Some unrelated table</tr></tbody>
    </table>

    <h2>Voting</h2>
    <div>
        <p>lol</p>
        <p>lol</p>
        <form>
            <table class="inline">
                <tbody><tr><td colspan="8">POOL IS CLOSED</td></tr></tbody>
            </table>
        </form>
    </div>
</div>
HTML;

        $extractor = $this->getInformationsFromHtml($html);
        $this->assertEquals(<<<'HTML'
<table
class="table table-striped table-hover"><tbody><tr>Some unrelated table</tr></tbody></table>
HTML
            , $extractor['contents']);
    }

    public function testCanStripChangelogFromContent()
    {
        $html = <<<'HTML'
<div class="page group">
<p>foobar</p>

<h2 class="sectionedit14" id="changelog">Changelog</h2>
<div class="level2">
<ul>
<li class="level1"><div class="li"> V0.1 Initial version</div>
</li>
<li class="level1"><div class="li"> V0.2 Adopted by Sean DuBois <a href="mailto:sean@siobud.com" class="mail" title="sean@siobud.com">sean@siobud.com</a></div>
</li>
</ul>

</div>
</div>
HTML;

        $extractor = $this->getInformationsFromHtml($html);
        $this->assertEquals(<<<'HTML'
<p>foobar</p>
HTML
            , $extractor['contents']);
    }

    public function testDoesntTruncateStuff()
    {
        $html         = $this->getDummyPage('rfc2');
        $informations = $this->getInformationsFromHtml($html);

        $this->assertContains('Similar discussion back in 2005', $informations['contents']);
    }

    public function testRequestIsVotingIfItHasVotes()
    {
        $html         = $this->getDummyPage('rfc');
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals(Request::VOTING, $informations['status']);
    }

    public function testRequestIsInactiveIfTooOld()
    {
        $html         = $this->getDummyPage('rfc2');
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals(Request::INACTIVE, $informations['status']);
    }

    public function testDoesntGoBeyondChangelogBlock()
    {
        $html         = $this->getDummyPage('rfc2');
        $informations = $this->getInformationsFromHtml($html);

        $this->assertCount(3, $informations['versions']);
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// DATA PROVIDERS //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    public function provideDates()
    {
        return [
            ['created at the DaTe   : 2014/01/02 lolmdr©', '2014-01-02 00:00:00'],
            ['Last update: May 9, 2011', '2011-05-09 00:00:00'],
            ['Date: June 07, 2010 (re-opened)', '2010-06-07 00:00:00'],
            ['Date: April, 16th 2013', '2013-04-16 00:00:00'],
            ['Date: 2011-17-07', '2011-07-17 00:00:00'],
            ['Date: 07/04 - 2010', '2010-07-04 00:00:00'],
            ['Date: 2012 Jan 8', '2012-01-08 00:00:00'],
            ['Last update: Fuck you', new DateTime()],
        ];
    }

    /**
     * @return array
     */
    public function provideTitles()
    {
        return [
            ['<h1>PHP RFC: Foobar</h1>', 'Foobar'],
            ['<h1>RFC: Foobar</h1>', 'Foobar'],
            ['<h1>Request for Comments: Foobar</h1>', 'Foobar'],
        ];
    }

    /**
     * @return array
     */
    public function provideStatuses()
    {
        return [
            ['Status: in draft', Request::DRAFT],
            ['Status: Under discussion', Request::DISCUSSION],
            ['Status: In voting phase', Request::VOTING],
            ['Status: Implemented (in PHP 7.0)', Request::APPROVED],
            ['Status: accepted (see voting results)', Request::APPROVED],
            ['Status: accepted', Request::APPROVED],
        ];
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Mock an informations block and get the informations from it.
     *
     * @param string $html
     *
     * @return array
     */
    protected function getInformationsFromInformationBlock($html)
    {
        return $this->getInformationsFromHtml('<div class="page group"><div class="level1"><ul><li>'.$html.'</li></ul></div></div>');
    }

    /**
     * Get the infromations from a piece of HTML.
     *
     * @param string $html
     *
     * @return array
     */
    protected function getInformationsFromHtml($html)
    {
        // Wrap in UTF8 if needed
        if (!strpos($html, 'DOCTYPE')) {
            $html = '<html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>';
        }

        // Create crawler and extract
        $crawler   = new Crawler($html);
        $extractor = new RequestExtractor($crawler);

        return $extractor->extract();
    }
}
