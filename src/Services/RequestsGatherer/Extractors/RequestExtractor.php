<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use History\Services\IdentityExtractor;

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
        $contents           = $this->getContents();
        $majorityConditions = $this->getMajorityConditions();
        $informations       = $this->getInformations();
        $status             = $this->getStatus($informations);
        $timestamp          = $this->getRequestTimestamp($informations);
        $authors            = $this->getAuthors($informations);

        // Extract questions
        $questions = $this->crawler->filterXpath('//table[@class="inline"]')->each(function ($question) {
            return (new QuestionExtractor($question))->extract();
        });

        return [
            'name'      => $name,
            'contents'  => $contents,
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
     * Get the HTML contents of the RFC.
     *
     * @return string
     */
    protected function getContents()
    {
        $contents = $this->crawler->filterXpath('//div[@class="page group"]');
        if (!$contents->count()) {
            return;
        }

        $contents = $contents->html();

        // Make tables into nice tables
        $contents = str_replace('<table class="inline">', '<table class="table table-striped table-hover">', $contents);

        // I'll have my own syntax highlighting, WITH BLACKJACK AND HOOKERS
        $this->crawler->filterXpath('//pre')->each(function ($code) use (&$contents) {
            $language = str_replace('code ', '', $code->attr('class'));
            $newLanguage = $language === 'c' ? 'cpp' : $language;

            $unformatted = htmlentities($code->text());
            $unformatted = '<pre><code class="'.$newLanguage.'">'.$unformatted.'</code></pre>';
            $contents = str_replace('<pre class="code '.$language.'">'.$code->html().'</pre>', $unformatted, $contents);
        });

        return $contents;
    }

    /**
     * Extract the RFC's informations.
     *
     * @return array
     */
    protected function getInformations()
    {
        $informations = [];
        $this->crawler->filterXpath('//div[@class="page group"]/div[@class="level1"]/ul/li')->each(function ($information) use (&$informations) {
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
        $status   = $this->findInformation($informations, '/Status/');
        $statuses = [
            'declined',
            'draft',
            'discussion',
            'voting',
            'accepted|implemented',
        ];

        // Look for a match in the status
        foreach ($statuses as $key => $matcher) {
            if (preg_match('/('.$matcher.')/i', $status)) {
                return $key;
            }
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
        $authors   = $this->findInformation($informations, '/Author/');
        $extractor = new IdentityExtractor($authors);

        return $extractor->extract();
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
        $locations = [
            '*[@id="proposed_voting_choices"]/following-sibling::div',
            '*[@id="vote"]/following-sibling::div/p'
        ];

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
