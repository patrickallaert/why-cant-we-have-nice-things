<?php

namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use Exception;
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

        $text = $crawler->filterXPath('//'.$selector);
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
        $information = preg_replace('/\s+/', ' ', $information);
        $information = trim($information);

        return $information;
    }

    /**
     * Attempt to parse a date from a bajillion different formats.
     *
     * @param string $text
     *
     * @return array
     */
    protected function parseDate($text)
    {
        // Cleanup string from shit
        $date = preg_replace('/\([a-z\-]+\)/i', '', $text);
        $date = preg_replace('/[^\d]*(\d{2,4}[-\/]\d{2}[-\/]\d{2,4}).*/i', '$1', $date);
        $date = str_replace('/', '-', $date);
        $date = preg_replace('/[,()]+/', ' ', $date);
        $date = trim($date);
        $date = $date ?: $text;

        // Loop over various formats until we
        // find one that fits
        $datetime = null;
        $formats  = [null, 'Y-d-m', 'm-d - Y', 'Y M d'];
        foreach ($formats as $format) {
            try {
                $datetime = $format
                    ? DateTime::createFromFormat($format, $date)
                    : new DateTime($date);

                if ($datetime) {
                    // Try to remove the date from the original text
                    $text = strtr($text, [
                        $datetime->format($format ?: 'Y-m-d') => '',
                        $datetime->format('Y-m-d')            => '',
                        $datetime->format('Y/m/d')            => '',
                        $datetime->format('d-m-Y')            => '',
                        $datetime->format('d/m/Y')            => '',
                    ]);
                    break;
                }
            } catch (Exception $exception) {
                // Else proceed to the next format
                continue;
            }
        }

        return [$datetime, $text];
    }
}
