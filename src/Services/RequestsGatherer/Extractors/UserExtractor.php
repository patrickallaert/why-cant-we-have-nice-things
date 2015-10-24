<?php
namespace History\Services\RequestsGatherer\Extractors;

use History\Services\RequestsGatherer\ExtractorInterface;

class UserExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        return [
            'full_name' => $this->extractText('h1'),
            'email'     => $this->extractText('.profile-details li:first-child'),
        ];
    }
}
