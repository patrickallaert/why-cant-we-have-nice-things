<?php

namespace History\Services\Internals;

use History\TestCase;

class ArticleParserTest extends TestCase
{
    /**
     * @dataProvider provideMessages
     *
     * @param string $before
     * @param string $after
     */
    public function testCanCleanupMessages($before, $after)
    {
        /** @var ArticleParser $cleaner */
        $cleaner = $this->container->get(ArticleParser::class);
        $cleaned = $cleaner->parse($before);

        $this->assertEquals($after, $cleaned['contents'].PHP_EOL);
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
            $this->getMessage(4),
        ];
    }
}
