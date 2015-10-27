<?php
namespace History\Services\RequestsGatherer\Extractors;

use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class RequestsExtractorTest extends TestCase
{
    public function testCanExtractRequests()
    {
        $crawler = new Crawler($this->getDummyPage('rfcs'));
        $extractor = new RequestsExtractor($crawler);
        $requests = $extractor->extract();

        $this->assertEquals([
            '/rfc/void_return_type',
            '/rfc/revisit-trailing-comma-function-args',
            '/rfc/closurefromcallable',
        ], $requests);
    }
}
