<?php

namespace History\Services\RequestsGatherer;

use History\CommandBus\Commands\CreateRequestCommand;
use History\Console\HistoryStyle;
use History\Entities\Models\Request;
use History\Services\RequestsGatherer\Extractors\RequestsExtractor;
use History\Services\Threading\OutputPool;
use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\DomCrawler\Crawler;

class RequestsGatherer
{
    /**
     * @var string
     */
    const DOMAIN = 'https://wiki.php.net';

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
        $this->output = new HistoryStyle();
    }

    /**
     * @param HistoryStyle $output
     */
    public function setOutput(HistoryStyle $output)
    {
        $this->output = $output;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// EXTRACTION /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get all the requests.
     *
     * @return Request[]
     */
    public function createRequests()
    {
        $crawler = $this->getPageCrawler(static::DOMAIN.'/rfc');
        if (!$crawler) {
            return;
        }

        $requests = (new RequestsExtractor($crawler))->extract();
        $pool = new OutputPool($this->output);
        foreach ($requests as $request) {
            $pool->submitCommand(new CreateRequestCommand(static::DOMAIN.$request));
        }

        return $pool->process();
    }

    /**
     * @param string $link
     *
     * @return Crawler
     */
    protected function getPageCrawler($link)
    {
        $contents = $this->cache->tags('php')->rememberForever($link, function () use ($link) {
            return file_get_contents($link);
        });

        return new Crawler($contents);
    }
}
