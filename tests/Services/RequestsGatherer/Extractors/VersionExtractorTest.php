<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class VersionExtractorTest extends TestCase
{
    public function testCanExtractVersionNumber()
    {
        $versions = $this->extractVersions(['V0.2 foo', 'V0.1 bar']);
        $this->assertEquals([
            ['version' => '0.2', 'name' => 'foo', 'timestamp' => false],
            ['version' => '0.1', 'name' => 'bar', 'timestamp' => false],
        ], $versions);

        $versions = $this->extractVersions(['v0.2.0 - foo', 'v0.1.0 - bar']);
        $this->assertEquals([
            ['version' => '0.2.0', 'name' => 'foo', 'timestamp' => false],
            ['version' => '0.1.0', 'name' => 'bar', 'timestamp' => false],
        ], $versions);

        $versions = $this->extractVersions(['Version 0.2: foo', 'Version 0.1: bar']);
        $this->assertEquals([
            ['version' => '0.2', 'name' => 'foo', 'timestamp' => false],
            ['version' => '0.1', 'name' => 'bar', 'timestamp' => false],
        ], $versions);

        $versions = $this->extractVersions(['foo', 'bar']);
        $this->assertEquals([
            ['version' => '2', 'name' => 'foo', 'timestamp' => false],
            ['version' => '1', 'name' => 'bar', 'timestamp' => false],
        ], $versions);
    }

    public function testCanExtractVersionDate()
    {
        $versions = $this->extractVersions(['2011-10-10 foo', '2011-10-05 bar']);
        $this->assertEquals([
            ['version' => 2, 'name' => 'foo', 'timestamp' => new DateTime('2011-10-10')],
            ['version' => 1, 'name' => 'bar', 'timestamp' => new DateTime('2011-10-05')],
        ], $versions);

        $versions = $this->extractVersions(['(2011-10-10): foo', '(2011-10-05): bar']);
        $this->assertEquals([
            ['version' => 2, 'name' => 'foo', 'timestamp' => new DateTime('2011-10-10')],
            ['version' => 1, 'name' => 'bar', 'timestamp' => new DateTime('2011-10-05')],
        ], $versions);

        $versions = $this->extractVersions(['10/10/2011 - foo', '05/10/2011 -  bar']);
        $this->assertEquals([
            ['version' => 2, 'name' => '10/10/2011 - foo', 'timestamp' => new DateTime('2011-10-10')],
            ['version' => 1, 'name' => '05/10/2011 - bar', 'timestamp' => new DateTime('2011-10-05')],
        ], $versions);
    }

    /**
     * @param array $versions
     *
     * @return array
     */
    protected function extractVersions(array $versions)
    {
        $versions = implode('</li><li class="level1">', $versions);
        $html     = <<<'HTML'
<h2 id="changelog">Changelog</h2>
<div class="level2">
    <ul>
        <li class="level1">
            $versions
        </li>
    </ul>
</div>
HTML;

        // Create crawler and extract
        $crawler   = new Crawler(str_replace('$versions', $versions, $html));
        $extractor = new VersionExtractor($crawler);

        return $extractor->extract();
    }
}
