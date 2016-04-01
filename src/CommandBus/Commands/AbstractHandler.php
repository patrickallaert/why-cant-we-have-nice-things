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
        $contents = $this->cache->tags('php')->rememberForever($link, function () use ($link) {
            $request = (new Client())->request('GET', $link);

            return $request->getBody()->getContents();
        });

        return new Crawler($contents);
    }
}
