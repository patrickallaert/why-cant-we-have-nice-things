<?php

namespace History\CommandBus\Commands;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractHandler
{
    /**
     * @param string $link
     *
     * @return Crawler
     */
    protected function getPageCrawler($link)
    {
        // Check the cache for the contents first
        if (!$contents = $this->cache->tags('php')->get($link)) {
            $request = (new Client())->request('GET', $link);
            $contents = $request->getBody()->getContents();

            $this->cache->tags('php')->forever($link, $contents);
        }

        return new Crawler($contents);
    }
}
