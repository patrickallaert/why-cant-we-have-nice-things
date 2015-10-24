<?php
namespace History\Services\RequestsGatherer\Extractors;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractExtractor
{
    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * RequestExtractor constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * @param $information
     *
     * @return string
     */
    protected function cleanWhitespace($information)
    {
        $information = Str::ascii($information);
        $information = preg_replace("/\s+/", ' ', $information);
        $information = trim($information);

        return $information;
    }
}
