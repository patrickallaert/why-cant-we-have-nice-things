<?php
namespace History\Services\RequestsGatherer;

use History\Services\RequestsGatherer\Extractors\UserExtractor;
use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class UserExtractorTest extends TestCase
{
    public function testCanExtractUserInformations()
    {
        $html      = file_get_contents(__DIR__.'/../../_pages/user.html');
        $crawler   = new Crawler($html);
        $extractor = new UserExtractor($crawler);

        $this->assertEquals([
            'full_name' => 'Maxime Fabre',
            'email'     => 'foo@bar.com',
        ], $extractor->extract());
    }
}
