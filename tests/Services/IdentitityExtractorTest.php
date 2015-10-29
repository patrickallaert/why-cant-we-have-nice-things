<?php
namespace History\Services;

use History\TestCase;

class IdentitityExtractorTest extends TestCase
{
    /**
     * @dataProvider provideIdentities
     *
     * @param string $text
     * @param array  $identities
     */
    public function testCanExtractVariousShittyFormats($text, array $identities)
    {
        $this->assertExtracted($text, $identities);
    }

    /**
     * @return array
     */
    public function provideIdentities()
    {
        return [
            [
                'Clint Priest <phpdev at zerocue dot com>',
                [
                    ['full_name' => 'Clint Priest', 'email' => 'phpdev@zerocue.com'],
                ],
            ],
            [
                'Rasmus Schultz rasmus@mindplay.dk, Yasuo Ohgaki yohgaki@ohgaki.net/yohgaki@php.net',
                [
                    ['full_name' => 'Rasmus Schultz', 'email' => 'rasmus@mindplay.dk'],
                    ['full_name' => 'Yasuo Ohgaki', 'email' => 'yohgaki@ohgaki.net'],
                    ['email' => 'yohgaki@php.net'],
                ],
            ],
            [
                '"Clint Priest" (phpdev at zerocue dot com)',
                [
                    ['full_name' => 'Clint Priest', 'email' => 'phpdev@zerocue.com'],
                ],
            ],
            [
                'Anthony Ferrara ircmaxell@php.net (original)',
                [
                    ['full_name' => 'Anthony Ferrara', 'email' => 'ircmaxell@php.net'],
                ]
            ]
        ];
    }

    /**
     * @param string $input
     * @param array  $output
     */
    private function assertExtracted($input, array $output)
    {
        $extractor = new IdentityExtractor($input);
        $this->assertEquals($output, $extractor->extract());
    }
}
