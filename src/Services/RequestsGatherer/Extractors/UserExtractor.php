<?php
namespace History\Services\RequestsGatherer\Extractors;

class UserExtractor extends AbstractExtractor
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        return [
            'username'  => $this->extractText('h2'),
            'full_name' => $this->extractText('h1'),
            'email'     => $this->extractText('.profile-details li:first-child'),
        ];
    }
}
