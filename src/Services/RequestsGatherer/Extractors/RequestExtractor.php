<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use DOMText;
use History\Services\IdentityExtractor;
use Minify_HTML;
use Symfony\Component\DomCrawler\Crawler;

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
        $majorityConditions = $this->getMajorityConditions();
        $informations       = $this->getInformations();
        $status             = $this->getStatus($informations);
        $timestamp          = $this->getRequestTimestamp($informations);
        $authors            = $this->getAuthors($informations);
        $versions           = $this->getVersions();

        // Extract questions
        $questions = $this->crawler->filterXpath('//form/table[@class="inline"]')->each(function ($question) {
            return (new QuestionExtractor($question))->extract();
        });

        return [
            'name'      => $name,
            'contents'  => $this->getContents(),
            'status'    => $status,
            'condition' => $majorityConditions,
            'authors'   => $authors,
            'timestamp' => $timestamp,
            'questions' => $questions,
            'versions'  => $versions,
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

        // Remove voting tables and changelog
        $this->removeSection($contents, '//div[form[table[@class="inline"]]]');
        $this->removeSection($contents, '//h2[@id="changelog"]/following-sibling::div');

        // Make tables into nice tables
        $contents->filterXPath('//table[@class="inline"]')->each(function (Crawler $table) {
            $table->getNode(0)->setAttribute('class', 'table table-striped table-hover');
        });

        // I'll have my own syntax highlighting, WITH BLACKJACK AND HOOKERS
        $html = $contents->html();
        $contents->filterXpath('//pre')->each(function (Crawler $pre) use (&$html) {
            $language = str_replace('code ', '', $pre->attr('class'));
            $newLanguage = $language === 'c' ? 'cpp' : $language;

            $code = htmlentities($pre->text());
            $code = '<pre><code class="'.$newLanguage.'">'.$code.'</code></pre>';
            $html = str_replace('<pre class="code '.$language.'">'.$pre->html().'</pre>', $code, $html);
        });

        // Minify contents
        $html = Minify_HTML::minify($html, [
            'xhtml' => false,
        ]);

        return $html;
    }

    /**
     * Extract the RFC's informations.
     *
     * @return array
     */
    protected function getInformations()
    {
        $informations = [];
        $lines        = $this->crawler->filterXpath('//div[@class="page group"]/div[@class="level1"]/ul/li');
        foreach ($lines as $line) {
            $text = str_replace("\n", ' ', $line->nodeValue);
            preg_match('/([^:]+) *: *(.+)/mi', $text, $matches);
            if (count($matches) < 3) {
                continue;
            }

            list(, $label, $value) = $matches;
            $label                 = $this->cleanWhitespace($label);
            $value                 = $this->cleanWhitespace($value);

            $informations[$label] = $value;
        }

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
        $statusText = $this->findInformation($informations, '/Status/');
        $statuses   = [
            0 => 'declined',
            1 => 'draft',
            2 => 'discussion',
            4 => 'accepted|implemented',
            3 => 'voting',
        ];

        // Look for a match in the status
        $status = 0;
        foreach ($statuses as $key => $matcher) {
            if (preg_match('/('.$matcher.')/i', $statusText)) {
                $status = $key;
                break;
            }
        }

        // If all polls are closed, then we're not voting
        // anymore and can consider implemented/declined
        if ($this->allPollsClosed() && $status === 3) {
            $status = 4;
        }

        return $status;
    }

    /**
     * @return bool
     */
    protected function allPollsClosed()
    {
        $polls  = $this->crawler->filterXPath('//form/table[@class="inline"]')->count();
        $closed = $this->crawler->filterXPath('//form/table/tbody/tr/td[@colspan]')->count();

        return $polls && $polls === $closed;
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
     * Get the RFC's versions.
     */
    protected function getVersions()
    {
        $crawler = $this->crawler->filterXPath('//h2[@id="changelog"]/following-sibling::div/ul');

        return (new VersionExtractor($crawler))->extract();
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
        // Find and cleanup date string
        $text           = $this->findInformation($informations, '/(created|date)/i');
        list($datetime) = $this->parseDate($text);

        // Try to grab date from footer
        $footer = $this->crawler->filterXPath('//div[@class="docInfo"]');
        if (!$datetime && $footer->count()) {
            $datetime = preg_replace('#.*(\d{4}/\d{2}/\d{2}).*#i', '$1', $footer->text());
            $datetime = DateTime::createFromFormat('Y/m/d', $datetime);
        }

        return $datetime ? $datetime->setTime(0, 0, 0) : new DateTime();
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
            '*[@id="vote"]/following-sibling::div/p',
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

    /**
     * Remove a section and its title from the contents.
     *
     * @param Crawler $contents
     * @param string  $selector
     */
    protected function removeSection(Crawler $contents, $selector)
    {
        $sections = $contents->filterXPath($selector);
        foreach ($sections as $section) {
            // Find title next to section div
            $previous = $section->previousSibling;
            while ($previous instanceof DOMText) {
                $previous = $previous->previousSibling;
            }

            // Remove title
            if ($previous) {
                $section->parentNode->removeChild($previous);
            }

            // Remove section
            $section->parentNode->removeChild($section);
        }
    }
}
