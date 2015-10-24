<?php
namespace History\Services\RequestsGatherer;

use DateTime;
use DateTimeZone;
use History\Services\RequestsGatherer\Extractors\RequestExtractor;
use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class RequestExtractorTest extends TestCase
{
    public function testCanGetRequestName()
    {
        $html         = file_get_contents(__DIR__.'/../../_pages/rfc.html');
        $informations = $this->getInformationsFromHtml($html);
        $timezone     = new DateTimeZone('UTC');

        $this->assertEquals([
            'name'      => 'Support Class Constant Visibility',
            'condition' => 'Simple Yes/No option. This requires a 2/3 majority.',
            'timestamp' => DateTime::createFromFormat('Y-m-d', '2015-09-13'),
            'authors'   => ['sean@php.net', 'reeze@php.net'],
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
    }

    public function testCanParseConditionsFromProposedVotingChoices()
    {
        $html         = '<div id="proposed_voting_choices"></div><div>Requires a 2/3 majority</div>';
        $informations = $this->getInformationsFromHtml($html);

        $this->assertEquals('Requires a 2/3 majority', $informations['condition']);
    }

    public function testCanParseWeirdAssDateFormats()
    {
        $html         = '<div class="page"><ul class="level1"><li>created at the DaTe   : 2014/01/02 lolmdr©</li></ul></div>';
        $informations = $this->getInformationsFromHtml($html);

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

    /**
     * Get the infromations from a piece of HTML.
     *
     * @param string $html
     *
     * @return array
     */
    protected function getInformationsFromHtml($html)
    {
        $crawler   = new Crawler($html);
        $extractor = new RequestExtractor($crawler);

        return $extractor->extract();
    }
}
