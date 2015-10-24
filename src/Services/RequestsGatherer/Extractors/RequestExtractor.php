<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DateTimeZone;
use History\Services\RequestsGatherer\ExtractorInterface;
use Symfony\Component\DomCrawler\Crawler;

class RequestExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * Get informations about an RFC.
     *
     * @return array
     */
    public function extract()
    {
        $name               = $this->getRequestName();
        $majorityConditions = $this->getMajorityConditions();
        $timestamp          = $this->getRequestTimestamp();
        $questions          = $this->getQuestions();

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

    /**
     * Get the questions asked on this RFC.
     *
     * @return array
     */
    protected function getQuestions()
    {
        return $this->crawler->filter('table.inline')->each(function ($question) {
            $name    = $this->extractText('tr:first-child', $question);
            $choices = $this->getChoices($question);

            return [
                'name'    => $name,
                'choices' => count($choices),
                'votes'   => $this->getVotes($question, $choices),
            ];
        });
    }

    /**
     * Get the choices available for this question.
     *
     * @param Crawler $question
     *
     * @return array
     */
    protected function getChoices(Crawler $question)
    {
        return $question->filter('tr.row1 td')->each(function ($choice) {
            return $choice->text();
        });
    }

    /**
     * Get the votes for a question.
     *
     * @param Crawler $question
     * @param array   $choices
     *
     * @return array
     */
    protected function getVotes(Crawler $question, array $choices)
    {
        $votes = [];

        $question
            ->filter('tr')
            ->reduce(function ($vote) {
                return $vote->filter('td.rightalign a')->count() > 0;
            })->each(function ($vote) use (&$votes, $choices) {
                $user = $vote->filter('td.rightalign a')->text();

                // Get which choice the user picked
                $voted = 0;
                $time  = new DateTime();
                $vote->filter('td')->each(function ($choice, $key) use (&$voted, &$time) {
                    $image = $choice->filter('img');
                    if ($image->count()) {
                        $timestamp = $image->attr('title');

                        $voted = $key;
                        $time  = $timestamp;
                    }
                });

                // Save vote for this request
                $votes[] = [
                    'user_id'    => $this->replaceFullnamesByUsernames($user),
                    'choice'     => $voted,
                    'created_at' => DateTime::createFromFormat('Y/m/d H:i', $time, new DateTimeZone('UTC')),
                ];
            });

        return $votes;
    }

    /**
     * Replace the full names by usernames for
     * the people who voted with their full names
     *
     * @param string $name
     *
     * @return string
     */
    protected function replaceFullnamesByUsernames($name)
    {
        return strtr($name, [
            'Barry Carlyon' => 'bcarlyon',
            'Ivan Enderlin' => 'hywan',
        ]);
    }
}
