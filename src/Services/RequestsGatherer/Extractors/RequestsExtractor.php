<?php

namespace History\Services\RequestsGatherer\Extractors;

class RequestsExtractor extends AbstractExtractor
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        return $this->crawler
            ->filterXpath('//li[@class="level1"]/div/a[@class="wikilink1"]')
            ->each(function ($request) {
                return $request->attr('href');
            });
    }
}
