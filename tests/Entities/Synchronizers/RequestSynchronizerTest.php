<?php
namespace History\Entities\Synchronizers;

use History\Entities\Models\Request;
use History\TestCase;

class RequestSynchronizerTest extends TestCase
{
    public function testCanSynchronizeRequest()
    {
        $sync = new RequestSynchronizer([
            'link'       => 'google.com',
            'name'       => 'foobar',
            'condition'  => '2/3',
            'status'     => 2,
            'timestamps' => '2015-01-01',
        ]);

        $request = $sync->synchronize();
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals([
            'link'       => 'google.com',
            'name'       => 'foobar',
            'contents'   => '',
            'condition'  => '2/3',
            'status'     => 2,
            'created_at' => '2015-01-01 00:00:00',
            'updated_at' => '2015-01-01 00:00:00',
        ], $request->toArray());
    }
}
