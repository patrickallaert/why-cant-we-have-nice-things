<?php
namespace History\Services\RequestsGatherer\Extractors;

use History\Services\RequestsGatherer\ExtractorInterface;

class QuestionExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        // Extract Question informations
        $name    = $this->extractText('tr:first-child');
        $choices = $this->getChoices();

        // Extract votes
        $votes = $this->crawler
            ->filter('tr')
            ->reduce(function ($vote) {
                return $vote->filter('td.rightalign a')->count() > 0;
            })->each(function ($vote) use (&$votes, $choices) {
                return (new VoteExtractor($vote))->extract();
            });

        return [
            'name'    => $name,
            'choices' => count($choices),
            'votes'   => $votes,
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
}
