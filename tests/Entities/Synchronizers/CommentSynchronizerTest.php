<?php

namespace History\Entities\Synchronizers;

use History\TestCase;

class CommentSynchronizerTest extends TestCase
{
    public function testCanSynchronizeComment()
    {
        $sync = new CommentSynchronizer([
            'xref' => '123',
            'subject' => 'foobar',
            'contents' => 'foobar',
            'user_id' => 1,
        ]);

        $comment = $sync->synchronize();
        $this->assertEquals([
            'xref' => '123',
            'references' => null,
            'name' => 'foobar',
            'contents' => 'foobar',
            'thread_id' => null,
            'comment_id' => null,
            'user_id' => 1,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
        ], $comment->toArray());
    }
}
