<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DateTimeZone;
use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class RequestExtractorTest extends TestCase
{
    public function testCanGetRequestName()
    {
        $html         = $this->getDummyPage('rfc');
        $informations = $this->getInformationsFromHtml($html);
        $timezone     = new DateTimeZone('UTC');

        $contents = $informations['contents'];
        unset($informations['contents']);

        $this->assertEquals([
            'name'      => 'Support Class Constant Visibility',
            'status'    => 2,
            'condition' => 'Simple Yes/No option. This requires a 2/3 majority.',
            'timestamp' => DateTime::createFromFormat('Y-m-d', '2015-09-13'),
            'authors'   => [
                ['full_name' => 'Sean DuBois', 'email' => 'sean@siobud.com'],
                ['full_name' => 'Reeze Xia', 'email' => 'reeze@php.net'],
            ],
            'questions' => [
                [
                    'name'    => 'Class Constant Visibility',
                    'choices' => 2,
                    'votes'   => [
                        [
                            'user_id'    => 'ajf',
                            'choice'     => 2,
                            'created_at' => DateTime::createFromFormat('Y-m-d H:i', '2015-10-22 22:30', $timezone),
                        ],
                        [
                            'user_id'    => 'ajf',
                            'choice'     => 1,
                            'created_at' => DateTime::createFromFormat('Y-m-d H:i', '2015-10-22 22:30', $timezone),
                        ],
                    ],
                ],
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
        $html         = '<div id="proposed_voting_choices"></div><div>Requires a 2/3 majority</div>';
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals('Requires a 2/3 majority', $informations['condition']);
    }

    public function testCanParseWeirdAssDateFormats()
    {
        $informations = $this->getInformationsFromInformationBlock('created at the DaTe   : 2014/01/02 lolmdr©');

        $this->assertEquals(DateTime::createFromFormat('Y-m-d', '2014-01-02'), $informations['timestamp']);
    }

    public function testCanCleanupRequestTitle()
    {
        $informations = $this->getInformationsFromHtml('<h1>PHP RFC: Foobar</h1>');
        $this->assertEquals('Foobar', $informations['name']);

        $informations = $this->getInformationsFromHtml('<h1>RFC: Foobar</h1>');
        $this->assertEquals('Foobar', $informations['name']);

        $informations = $this->getInformationsFromHtml('<h1>Request for Comments: Foobar</h1>');
        $this->assertEquals('Foobar', $informations['name']);
    }

    public function testCanParseStatus()
    {
        $informations = $this->getInformationsFromInformationBlock('Status: in draft');
        $this->assertEquals(1, $informations['status']);

        $informations = $this->getInformationsFromInformationBlock('Status: Under discussion');
        $this->assertEquals(2, $informations['status']);

        $informations = $this->getInformationsFromInformationBlock('Status: In voting phase');
        $this->assertEquals(3, $informations['status']);

        $informations = $this->getInformationsFromInformationBlock('Status: Implemented (in PHP 7.0)');
        $this->assertEquals(4, $informations['status']);

        $informations = $this->getInformationsFromInformationBlock('Status: accepted');
        $this->assertEquals(4, $informations['status']);
    }

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
