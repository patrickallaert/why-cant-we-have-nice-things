<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DateTimeZone;
use History\Services\RequestsGatherer\ExtractorInterface;

class VoteExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        $user = $this->extractText('td.rightalign a');

        // Get which choice the user picked
        $voted = 0;
        $time  = new DateTime();
        $this->crawler->filter('td')->each(function ($choice, $key) use (&$voted, &$time) {
            $image = $choice->filter('img');

            if ($image->count()) {
                $timestamp = $image->attr('title');

                $voted = $key;
                $time  = $timestamp;
            }
        });

        // Save vote for this request
        return [
            'user_id'    => $this->replaceFullnamesByUsernames($user),
            'choice'     => $voted,
            'created_at' => DateTime::createFromFormat('Y/m/d H:i', $time, new DateTimeZone('UTC')),
        ];
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
