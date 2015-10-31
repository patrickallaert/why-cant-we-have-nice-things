<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\TestCase;

class CommentSynchronizerTest extends TestCase
{
    public function testCanSynchronizeComment()
    {
        $sync = new CommentSynchronizer([
            'xref'     => '123',
            'subject'  => 'foobar',
            'contents' => 'foobar',
            'user_id'  => 1,
            'date'     => 'fuck you (GMT Daylight Time)',
        ]);

        $comment = $sync->synchronize();
        $this->assertEquals([
            'xref'       => '123',
            'name'       => 'foobar',
            'contents'   => 'foobar',
            'request_id' => null,
            'comment_id' => null,
            'user_id'    => 1,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
        ], $comment->toArray());
    }
}
