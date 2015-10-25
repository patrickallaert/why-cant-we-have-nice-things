<?php
namespace History\Services\RequestsGatherer\Extractors;

use History\Services\RequestsGatherer\ExtractorInterface;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractExtractor implements ExtractorInterface
{
    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * @param string       $selector
     * @param Crawler|null $crawler
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
     * @param string $information
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
