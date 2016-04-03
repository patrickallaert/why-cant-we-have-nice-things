<?php

namespace History\Entities\Models;

use History\Entities\Models\Threads\Comment;
use History\TestCase;
use League\CommonMark\CommonMarkConverter;
use LogicException;
use Mockery;

class CommentTest extends TestCase
{
    public function testCanConvertCommentToHtml()
    {
        $comment = new Comment([
            'contents' => '# foobar',
        ]);

        $this->assertEquals('<h1>foobar</h1>'.PHP_EOL, $comment->parsed_contents);
    }

    public function testReturnsOriginalContentIfNotParseable()
    {
        $contents = '# foobar';
        $comment = new Comment([
            'contents' => $contents,
        ]);

        $converter = Mockery::mock(CommonMarkConverter::class);
        $converter->shouldReceive('convertToHtml')->andThrow(LogicException::class);

        $this->assertEquals($contents, $comment->getParsedContentsAttribute($converter));
    }
}
