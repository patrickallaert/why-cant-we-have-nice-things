<?php

namespace History\Services\RequestsGatherer\Extractors;

class TargetExtractor extends AbstractExtractor
{
    /**
     * Extract informations about something.
     *
     * @return string|null
     */
    public function extract()
    {
        if (!$this->crawler->count()) {
            return;
        }

        // Clean text and replace foo.next by foo.x
        $text = $this->normalizeText($this->crawler->text());

        // Look for a direct match, if not look for X numbers version
        $target = $this->getDirectMatch($text) ?: $this->getIndirectMatch($text);
        $target = $this->normalizeText($target);

        return $this->normalizeVersion($target);
    }

    /**
     * Get a direct match from the text (ie a clear definitive version).
     *
     * @param string $text
     *
     * @return string|null
     */
    protected function getDirectMatch(string $text)
    {
        $target = preg_replace('/.*php ?(\d)(\.[0-9x])?.*/', '$1$2', $text);

        return $target !== $text ? $target : null;
    }

    /**
     * Get an indirect match (ie. something that sorta look like a version).
     *
     * @param string $text
     *
     * @return string|null
     */
    protected function getIndirectMatch(string $text)
    {
        $target = null;
        foreach ([5, 3, 1] as $size) {
            $target = $this->matchVersionOfSize($text, $size);

            // If we have a match and it doesn't look fishy, keep it
            if ($target !== $text || !preg_match('/([ &a-z]{2,}|[0-9a-z]{2,})/', $target)) {
                break;
            }
        }

        return $target;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// NORMALIZERS ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Normalize some things in the text
     * and unify .next to .x.
     *
     * @param string $text
     *
     * @return string
     */
    protected function normalizeText(string $text): string
    {
        $text = $this->cleanWhitespace($text);
        $text = str_replace('.next', '.x', $text);
        $text = strtolower(trim($text, '.'));

        return $text;
    }

    /**
     * Cleanup and normalize version to X.X(.X).
     *
     * @param string $target
     *
     * @return string|null
     */
    protected function normalizeVersion(string $target)
    {
        $target = strlen($target) === 1 ? $target.'.0' : $target;
        $target = preg_replace('/([0-9\.x]{3})\.0/', '$1', $target);
        if ($target === 'x.0' || preg_match('/^[0-2\.a-z ]+$/', $target)) {
            return;
        }

        return trim($target, '.');
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $text
     * @param int    $size
     *
     * @return string
     */
    protected function matchVersionOfSize(string $text, int $size): string
    {
        return preg_replace('/.*([0-9x\.]{'.$size.'}).*/', '$1', $text);
    }
}
