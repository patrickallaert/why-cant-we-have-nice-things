<?php

namespace History\Services\RequestsGatherer\Extractors;

class VersionExtractor extends AbstractExtractor
{
    /**
     * Extract informations about something.
     *
     * @return array
     */
    public function extract()
    {
        // Extract versions from body
        $versions = $this->crawler->filterXPath('//li')->extract(['_text']);

        foreach ($versions as $key => $version) {
            $version = $this->cleanWhitespace($version);
            preg_match('/(V|Version)? ?(\d*\.*\d+\.\d+):? (.+)/i', $version, $matches);
            if (count($matches) === 4) {
                list(, , $version, $name) = $matches;
            } else {
                // If there is no actual version number
                // use the key
                $name = $version;
                $version = count($versions) - $key;
            }

            list($date, $name) = $this->parseDate($name);
            $name = trim($name, ' -():');

            $versions[$key] = [
                'version' => $version,
                'name' => $name,
                'timestamps' => $date,
            ];
        }

        return $versions;
    }
}
