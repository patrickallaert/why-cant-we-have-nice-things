<?php
namespace History\RequestsGatherer;

use DateTime;
use History\Entities\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class InformationsExtractor
{
    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * InformationsExtractor constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Get informations about an RFC
     *
     * @return array
     */
    public function getRequestInformations()
    {
        $name               = $this->getRequestName();
        $majorityConditions = $this->cleanWhitespace($this->getMajorityConditions());
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
        $title = $this->crawler->filter('h1');
        if (!$title->count()) {
            return;
        }

        // Remove some tags from title
        $title = $title->text();
        $title = str_replace('PHP RFC:', '', $title);
        $title = str_replace('RFC:', '', $title);
        $title = str_replace('Request for Comments:', '', $title);

        return trim($title);
    }

    /**
     * Get the creation/update date of a request.
     * This is pretty dirty since nobody thought about agreeing
     * on a date format so we have a bit of everything
     *
     * @return DateTime
     */
    protected function getRequestTimestamp()
    {
        $timestamp = null;
        $this->crawler->filter('.level1 li')->each(function ($information) use (&$timestamp) {
            $text = $information->text();
            $date = preg_replace('/.*(created|date) ?: +(\d{4}-\d{2}-\d{2}).*/i', '$2', $text);
            $date = str_replace('/', '-', $date);
            $date = trim($date);

            if (strpos($date, '20') === 0) {
                $timestamp = $date;
            }
        });

        return DateTime::createFromFormat('Y-m-d', $timestamp);
    }

    /**
     * Get the majority conditions (50%+1 or 2/3)
     *
     * @return string|void
     */
    protected function getMajorityConditions()
    {
        $condition = $this->crawler->filter('#proposed_voting_choices + div');
        if ($condition->count()) {
            return $condition->text();
        }

        $condition = $this->crawler->filter('#vote + div p');
        if ($condition->count()) {
            return $condition->text();
        }
    }

    /**
     * Get the questions asked on this RFC
     *
     * @return array
     */
    protected function getQuestions()
    {
        return $this->crawler->filter('table.inline')->each(function ($question) {
            $name = $question->filter('tr:first-child')->text();
            $name = $this->cleanWhitespace($name);

            return [
                'name'  => $name,
                'votes' => $this->getVotes($question),
            ];
        });
    }

    /**
     * Get the votes for a question
     *
     * @param $question
     *
     * @return array
     */
    protected function getVotes(Crawler $question)
    {
        $votes   = [];
        $choices = $question->filter('tr.row1 td')->each(function ($choice) {
            return $choice->text();
        });

        $question
            ->filter('tr')
            ->reduce(function ($vote) {
                return $vote->filter('td.rightalign a')->count() > 0;
            })->each(function ($vote) use (&$votes, $choices) {
                $user  = $vote->filter('td.rightalign a')->text();
                $voted = !$vote->filter('td:last-child img')->count();

                // Create user
                $user = User::firstOrCreate([
                    'name' => $user,
                ]);

                // Save vote for this request
                $votes[] = [
                    'user_id'    => $user->id,
                    'vote'       => $voted,
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                ];
            });

        return $votes;
    }

    /**
     * @param $information
     *
     * @return string
     */
    protected function cleanWhitespace($information)
    {
        $information = Str::ascii($information);
        $information = preg_replace("/\s+/", ' ', $information);
        $information = trim($information);

        return $information;
    }
}
