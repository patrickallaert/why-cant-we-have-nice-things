<?php

namespace History\Services\RequestsGatherer;

use History\Console\HistoryStyle;
use History\Entities\Models\Request;
use History\Services\RequestsGatherer\Extractors\RequestsExtractor;
use History\Services\Threading\Pool;
use Illuminate\Contracts\Cache\Repository;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param Repository         $cache
     */
    public function __construct(ContainerInterface $container, Repository $cache)
    {
        $this->cache  = $cache;
        $this->output = new HistoryStyle(new ArrayInput([]), new NullOutput());
        $this->container = $container;
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
        $pool     = new Pool($this->container, $this->output);
        $requests = array_slice($requests, 0, 50);
        foreach ($requests as $request) {
            $pool->queue(CreateRequest::class, [
               'request' => static::DOMAIN.$request,
            ]);
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
