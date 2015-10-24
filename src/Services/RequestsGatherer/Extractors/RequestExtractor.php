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
        $timestamp          = $this->getRequestTimestamp();

        // Extract questions
        $questions = $this->crawler->filter('table.inline')->each(function ($question) {
            return (new QuestionExtractor($question))->extract();
        });

        return [
            'name'      => $name,
            'condition' => $majorityConditions,
            'timestamp' => $timestamp,
            'questions' => $questions,
        ];
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

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
     * Get the creation/update date of a request.
     * This is pretty dirty since nobody thought about agreeing
     * on a date format so we have a bit of everything.
     *
     * @return DateTime
     */
    protected function getRequestTimestamp()
    {
        $timestamp = null;
        $this->crawler->filter('.level1 li')->each(function ($information) use (&$timestamp) {
            $text = $information->text();
            $date = preg_replace('/.*(created|date) *: +(\d{4}[-\/]\d{2}[-\/]\d{2}).*/i', '$2', $text);
            $date = str_replace('/', '-', $date);
            $date = trim($date);

            if (strpos($date, '20') === 0) {
                $timestamp = $date;
            }
        });

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
