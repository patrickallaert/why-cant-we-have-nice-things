<?php

namespace History\Services\RequestsGatherer;

use History\CommandBus\Commands\CreateRequestCommand;
use History\Console\HistoryStyle;
use History\Entities\Models\Request;
use History\Services\RequestsGatherer\Extractors\RequestsExtractor;
use History\Services\Traits\HasAsyncCapabilitiesTrait;
use Illuminate\Contracts\Cache\Repository;
use League\Tactician\CommandBus;
use Symfony\Component\DomCrawler\Crawler;

class RequestsGatherer
{
    use HasAsyncCapabilitiesTrait;

    /**
     * @var string
     */
    const DOMAIN = 'https://wiki.php.net';

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @param Repository $cache
     * @param CommandBus $bus
     */
    public function __construct(Repository $cache, CommandBus $bus = null)
    {
        $this->cache = $cache;
        $this->output = new HistoryStyle();
        $this->bus = $bus;
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
        $commands = array_map(function ($request) {
            return new CreateRequestCommand(static::DOMAIN.$request);
        }, $requests);

        return $this->dispatchCommands($commands);
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
