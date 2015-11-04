<?php

namespace History\Services\RequestsGatherer;

interface ExtractorInterface
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract();
}
