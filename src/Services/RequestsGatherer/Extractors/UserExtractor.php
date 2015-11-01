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
            'name'          => $this->extractText('h2'),
            'full_name'     => $this->extractText('h1'),
            'email'         => $this->extractText('ul[@class="profile-details"]/li[position() = 1]'),
            'contributions' => $this->getContributions(),
        ];
    }

    /**
     * Get the user's contributions.
     *
     * @return string[]
     */
    private function getContributions()
    {
        return $this->crawler->filterXpath('//h2[@id="karma"]/following-sibling::ul/li/a')->each(function ($contribution
        ) {
            return $contribution->text();
        });
    }
}
