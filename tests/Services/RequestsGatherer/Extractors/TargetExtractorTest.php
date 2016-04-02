<?php

namespace History\Services\RequestsGatherer\Extractors;

use History\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class TargetExtractorTest extends TestCase
{
    /**
     * @dataProvider provideTargets
     */
    public function testCanExtractTarget($expected, $html)
    {
        $extractor = new TargetExtractor(new Crawler($html));
        $target = $extractor->extract();

        $this->assertEquals($expected, $target);
    }

    /**
     * @return array
     */
    public function provideTargets()
    {
        return [
            ['7.0', 'Targeting the next minor version. (In this case PHP 7, being a major too.)'],
            ['6.0', 'PHP6 (or whatever next major is called)'],
            ['7.1', 'PHP 7.1'],
            ['7.0', 'PHP 7'],
            ['7.1', 'An ideal target version would be 7.1.0'],
            ['7.x', 'This proposed for the next PHP 7.x.'],
            ['5.x', '5.next'],
            ['5.7', '5.7 or later'],
            ['7.x', 'Target: PHP7.x'],
            ['7.0', '5.7 & 7.0'],
            ['5.x', '5.next (master branch)'],
            [null, 'master branch'],
            ['5.6', 'Implemented (PHP-5.6)'],
            ['7.0', '7.0.0'],
            ['7.0', 'PHP 7'],
            [null, 'PHP internals'],
            ['8.0', 'PHP 8.0 (for actual implementation and corresponding BC break).'],
            ['7.x', '- PHP 7.X'],
            [null, '0.1'],
            [null, '012'],
        ];
    }
}
