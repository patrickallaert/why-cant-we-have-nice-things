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
        $fullName = $this->getFullName();
        $email    = $this->getEmail();

        return [
            'full_name' => $fullName,
            'email'     => $email,
        ];
    }

    /**
     * @return string
     */
    private function getFullName()
    {
        $name = $this->crawler->filter('h1');
        $name = $name->count() ? $name->text() : '';
        $name = $this->cleanWhitespace($name);

        return $name;
    }

    /**
     * @return string
     */
    private function getEmail()
    {
        $email = $this->crawler->filter('.profile-details li:first-child');
        $email = $email->count() ? $email->text() : '';
        $email = $this->cleanWhitespace($email);

        return $email;
    }
}
