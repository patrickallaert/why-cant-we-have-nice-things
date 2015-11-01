<?php
namespace History\Services\Internals;

use History\TestCase;

class MailingListArticleCleanerTest extends TestCase
{

    /**
     * @dataProvider provideMessages
     *
     * @param string $before
     * @param string $after
     */
    public function testCanCleanupMessages($before, $after)
    {
        $cleaner = new MailingListArticleCleaner();
        $cleaned = $cleaner->cleanup(explode(PHP_EOL, $before));

        $this->assertEquals($after, $cleaned.PHP_EOL);
    }

    /**
     * @return array
     */
    public function provideMessages()
    {
        return [
            $this->getMessage(0),
            $this->getMessage(1),
            $this->getMessage(2),
            $this->getMessage(3),
        ];
    }
}
