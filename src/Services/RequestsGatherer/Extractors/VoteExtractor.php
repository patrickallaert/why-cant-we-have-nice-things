<?php

namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DateTimeZone;

class VoteExtractor extends AbstractExtractor
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        $user = $this->extractText('td[@class="rightalign"]/a');

        // Get which choice the user picked
        $voted = 0;
        $time = (new DateTime())->format('Y/m/d H:i:s');
        $this->crawler->filterXpath('//td')->each(function ($choice, $key) use (&$voted, &$time) {
            $image = $choice->filterXpath('//img');

            if ($image->count()) {
                $timestamp = $image->attr('title');

                $voted = $key;
                $time = $timestamp;
            }
        });

        // Save vote for this request
        return [
            'user_id' => $this->replaceFullnamesByUsernames($user),
            'choice' => $voted,
            'timestamps' => DateTime::createFromFormat('Y/m/d H:i', $time, new DateTimeZone('UTC')),
        ];
    }

    /**
     * Replace the full names by usernames for
     * the people who voted with their full names.
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
