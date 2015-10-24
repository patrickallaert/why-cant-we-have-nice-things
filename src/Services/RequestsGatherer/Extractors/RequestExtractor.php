<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use History\Services\RequestsGatherer\ExtractorInterface;

class RequestExtractor extends AbstractExtractor implements ExtractorInterface
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
        $timestamp          = $this->getRequestTimestamp($informations);
        $authors            = $this->getAuthors($informations);

        // Extract questions
        $questions = $this->crawler->filter('table.inline')->each(function ($question) {
            return (new QuestionExtractor($question))->extract();
        });

        return [
            'name'      => $name,
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
     * Extract the RFC's informations
     *
     * @return array
     */
    protected function getInformations()
    {
        $informations = [];
        $this->crawler->filter('.level1 li')->each(function ($information) use (&$informations) {
            $text = $information->text();
            $text = str_replace("\n", ' ', $text);

            preg_match('/([^:]+) *: *(.+)/mi', $text, $matches);
            list (, $label, $value) = $matches;

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
     * @return array
     */
    protected function getAuthors(array $informations)
    {
        $authors = [];
        foreach ($informations as $label => $value) {
            if (strpos($label, 'Author') === false) {
                continue;
            }

            $authors = explode(',', $value);
            foreach ($authors as $key => $author) {
                $author        = trim($author);
                $authors[$key] = last(explode(' ', $author));
            }
        }

        return $authors;
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
        $timestamp = null;
        foreach ($informations as $label => $value) {
            if (!preg_match('/(created|date)/i', $label)) {
                continue;
            }

            $date = preg_replace('/(\d{4}[-\/]\d{2}[-\/]\d{2}).*/i', '$1', $value);
            $date = str_replace('/', '-', $date);
            $date = trim($date);

            if (strpos($date, '20') === 0) {
                $timestamp = $date;
            }
        }

        return DateTime::createFromFormat('Y-m-d', $timestamp);
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
}
