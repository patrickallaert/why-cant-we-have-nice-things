<?php

namespace History\Services\RequestsGatherer\Extractors;

use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class UserExtractorTest extends TestCase
{
    public function testCanExtractUserInformations()
    {
        $html      = $this->getDummyPage('user');
        $crawler   = new Crawler($html);
        $extractor = new UserExtractor($crawler);

        $this->assertEquals([
            'name'          => 'anahkiasen',
            'full_name'     => 'Maxime Fabre',
            'email'         => 'foo@bar.com',
            'contributions' => ['pear/packages', 'pear/peardoc'],
        ], $extractor->extract());
    }
}
