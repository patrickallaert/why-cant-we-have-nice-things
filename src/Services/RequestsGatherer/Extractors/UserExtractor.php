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
            'username'      => $this->extractText('h2'),
            'full_name'     => $this->extractText('h1'),
            'email'         => $this->extractText('.profile-details li:first-child'),
            'contributions' => $this->getContributions(),
        ];
    }

    /**
     * Get the user's contributions
     *
     * @return string[]
     */
    private function getContributions()
    {
        return $this->crawler->filter('#karma + ul a')->each(function ($contribution) {
            return $contribution->text();
        });
    }
}
