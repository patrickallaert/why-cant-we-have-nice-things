<?php
namespace History\Services\RequestsGatherer\Extractors;

use DateTime;
use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class VersionExtractorTest extends TestCase
{
    /**
     * @dataProvider provideVersions
     *
     * @param array $versions
     * @param array $expected
     */
    public function testCanExtractVersionNumber(array $versions, array $expected)
    {
        $versions = $this->extractVersions($versions);
        $this->assertEquals($expected, $versions);
    }

    /**
     * @return array
     */
    public function provideVersions()
    {
        return [
            [
                ['V0.2 foo', 'V0.1 bar'],
                [
                    ['version' => '0.2', 'name' => 'foo', 'timestamp' => false],
                    ['version' => '0.1', 'name' => 'bar', 'timestamp' => false],
                ],
            ],
            [
                ['v0.2.0 - foo', 'v0.1.0 - bar'],
                [
                    ['version' => '0.2.0', 'name' => 'foo', 'timestamp' => false],
                    ['version' => '0.1.0', 'name' => 'bar', 'timestamp' => false],
                ],
            ],
            [
                ['Version 0.2: foo', 'Version 0.1: bar'],
                [
                    ['version' => '0.2', 'name' => 'foo', 'timestamp' => false],
                    ['version' => '0.1', 'name' => 'bar', 'timestamp' => false],
                ],
            ],
            [
                ['foo', 'bar'],
                [
                    ['version' => '2', 'name' => 'foo', 'timestamp' => false],
                    ['version' => '1', 'name' => 'bar', 'timestamp' => false],
                ],
            ],
            [
                ['2011-10-10 foo', '2011-10-05 bar'],
                [
                    ['version' => 2, 'name' => 'foo', 'timestamp' => new DateTime('2011-10-10')],
                    ['version' => 1, 'name' => 'bar', 'timestamp' => new DateTime('2011-10-05')],
                ],
            ],
            [
                ['(2011-10-10): foo', '(2011-10-05): bar'],
                [
                    ['version' => 2, 'name' => 'foo', 'timestamp' => new DateTime('2011-10-10')],
                    ['version' => 1, 'name' => 'bar', 'timestamp' => new DateTime('2011-10-05')],
                ],
            ],
            [
                ['10/10/2011 - foo', '05/10/2011 -  bar'],
                [
                    ['version' => 2, 'name' => '10/10/2011 - foo', 'timestamp' => new DateTime('2011-10-10')],
                    ['version' => 1, 'name' => '05/10/2011 - bar', 'timestamp' => new DateTime('2011-10-05')],
                ],
            ],
        ];
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
