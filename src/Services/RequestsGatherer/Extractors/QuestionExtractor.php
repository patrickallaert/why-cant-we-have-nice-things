<?php
namespace History\Services\RequestsGatherer\Extractors;

class QuestionExtractor extends AbstractExtractor
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        // Extract Question informations
        $name    = $this->extractText('tr[position() = 1]');
        $choices = $this->getChoices();

        // Extract votes
        $votes = $this->crawler
            ->filterXpath('//tr')
            ->reduce(function ($vote) {
                return $vote->filterXpath('//td/img')->count() > 0;
            })->each(function ($vote) use (&$votes, $choices) {
                return (new VoteExtractor($vote))->extract();
            });

        return [
            'name'    => $name,
            'choices' => $choices,
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
        return $this->crawler->filterXpath('//tr[@class="row1"]/td')->each(function ($choice) {
            return trim($choice->text(), ' ?!');
        });
    }
}
