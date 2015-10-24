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
     * @param string $selector
     * @param Crawler|null   $crawler
     *
     * @return string
     */
    protected function extractText($selector, $crawler = null)
    {
        $crawler = $crawler ?: $this->crawler;

        $text = $crawler->filter($selector);
        $text = $text->count() ? $text->text() : '';
        $text = $this->cleanWhitespace($text);

        return $text;
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
