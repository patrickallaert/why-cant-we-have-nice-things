<?php

namespace History\Entities\Models;

use History\TestCase;

class CommentTest extends TestCase
{
    public function testCanConvertCommentToHtml()
    {
        $comment = new Comment([
            'contents' => '# foobar',
        ]);

        $this->assertEquals('<h1>foobar</h1>'.PHP_EOL, $comment->parsed_contents);
    }
}
