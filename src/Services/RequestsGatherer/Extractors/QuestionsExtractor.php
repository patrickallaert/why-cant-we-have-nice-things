<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DateTimeZone;
use History\Services\RequestsGatherer\ExtractorInterface;

class QuestionsExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        $name    = $this->extractText('tr:first-child');
        $choices = $this->getChoices();

        return [
            'name'    => $name,
            'choices' => count($choices),
            'votes'   => $this->getVotes($choices),
        ];
    }

    /**
     * Get the choices available for this question.
     *
     * @return array
     */
    protected function getChoices()
    {
        return $this->crawler->filter('tr.row1 td')->each(function ($choice) {
            return $choice->text();
        });
    }

    /**
     * Get the votes for a question.
     *
     * @param array $choices
     *
     * @return array
     */
    protected function getVotes(array $choices)
    {
        $votes = [];

        $this->crawler
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
