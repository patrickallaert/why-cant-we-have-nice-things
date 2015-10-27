<?php
namespace History\Services;

use History\TestCase;

class IdentitityExtractorTest extends TestCase
{
    public function testCanExtractVariousShittyFormats()
    {
        $this->assertExtracted('Clint Priest <phpdev at zerocue dot com>', [
            ['full_name' => 'Clint Priest', 'email' => 'phpdev@zerocue.com']
        ]);

        $this->assertExtracted('Rasmus Schultz rasmus@mindplay.dk, Yasuo Ohgaki yohgaki@ohgaki.net/yohgaki@php.net', [
            ['full_name' => 'Rasmus Schultz', 'email' => 'rasmus@mindplay.dk'],
            ['full_name' => 'Yasuo Ohgaki', 'email' => 'yohgaki@ohgaki.net'],
            ['email' => 'yohgaki@php.net']
        ]);
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
