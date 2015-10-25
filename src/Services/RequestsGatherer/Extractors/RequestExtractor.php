<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;

class RequestExtractor extends AbstractExtractor
{
    /**
     * Get informations about an RFC.
     *
     * @return array
     */
    public function extract()
    {
        // Extract Request informations
        $name               = $this->getRequestName();
        $majorityConditions = $this->getMajorityConditions();
        $informations       = $this->getInformations();
        $status             = $this->getStatus($informations);
        $timestamp          = $this->getRequestTimestamp($informations);
        $authors            = $this->getAuthors($informations);

        // Extract questions
        $questions = $this->crawler->filter('table.inline')->each(function ($question) {
            return (new QuestionExtractor($question))->extract();
        });

        return [
            'name'      => $name,
            'status'    => $status,
            'condition' => $majorityConditions,
            'authors'   => $authors,
            'timestamp' => $timestamp,
            'questions' => $questions,
        ];
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Extract the RFC's informations.
     *
     * @return array
     */
    protected function getInformations()
    {
        $informations = [];
        $this->crawler->filter('.page .level1 li')->each(function ($information) use (&$informations) {
            $text = $information->text();
            $text = str_replace("\n", ' ', $text);

            preg_match('/([^:]+) *: *(.+)/mi', $text, $matches);
            if (count($matches) < 3) {
                return;
            }

            list(, $label, $value) = $matches;
            $label = $this->cleanWhitespace($label);
            $value = $this->cleanWhitespace($value);

            $informations[$label] = $value;
        });

        return $informations;
    }

    /**
     * Extract the name of a request.
     *
     * @return string
     */
    protected function getRequestName()
    {
        $title = $this->extractText('h1');
        $title = str_replace('PHP RFC:', '', $title);
        $title = str_replace('RFC:', '', $title);
        $title = str_replace('Request for Comments:', '', $title);

        return trim($title);
    }

    /**
     * @param array $informations
     *
     * @return int
     */
    protected function getStatus(array $informations)
    {
        $status = $this->findInformation($informations, '/Status/');
        if (preg_match('/(draft|discussion)/i', $status)) {
            return 1;
        } elseif (preg_match('/(accepted|implemented)/i', $status)) {
            return 2;
        }

        return 0;
    }

    /**
     * Here we try to retrieve an RFC's author. As usual since there
     * is no real defined format to present the authors, we have to do
     * a lot of guessing and cleanup.
     *
     * @param array $informations
     *
     * @return array
     */
    protected function getAuthors(array $informations)
    {
        $authors = $this->findInformation($informations, '/Author/');

        // Try to split off authors
        $authors = preg_replace('/[\(\)]/', '', $authors);
        $authors = preg_split('/[\s,]+/', $authors);
        foreach ($authors as $key => $author) {

            // Get email from the author's name
            $author = trim($author);
            $author = preg_replace('/@(.+)/', '@php.net', $author);
            $author = filter_var($author, FILTER_VALIDATE_EMAIL) ? $author : null;

            $authors[$key] = $author;
        }

        return array_values(array_filter($authors));
    }

    /**
     * Get the creation/update date of a request.
     * This is pretty dirty since nobody thought about agreeing
     * on a date format so we have a bit of everything.
     *
     * @param array $informations
     *
     * @return DateTime
     */
    protected function getRequestTimestamp(array $informations)
    {
        $date = $this->findInformation($informations, '/(created|date)/i');
        $date = preg_replace('/(\d{4}[-\/]\d{2}[-\/]\d{2}).*/i', '$1', $date);
        $date = str_replace('/', '-', $date);
        $date = trim($date);

        if (strpos($date, '20') === 0) {
            return DateTime::createFromFormat('Y-m-d', $date);
        }
    }

    /**
     * Get the majority conditions (50%+1 or 2/3).
     *
     * @return string|void
     */
    protected function getMajorityConditions()
    {
        $locations = ['#proposed_voting_choices + div', '#vote + div p'];
        foreach ($locations as $location) {
            if ($text = $this->extractText($location)) {
                return $text;
            }
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Find a value in the informations array.
     *
     * @param array  $informations
     * @param string $matcher
     *
     * @return string
     */
    protected function findInformation(array $informations, $matcher)
    {
        foreach ($informations as $label => $value) {
            if (preg_match($matcher, $label)) {
                return $value;
            }
        }
    }
}
