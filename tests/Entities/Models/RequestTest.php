<?php
namespace History\Entities\Models;

use History\TestCase;

class RequestTest extends TestCase
{
    public function testCanGetRequestLabel()
    {
        $request = new Request(['status' => 0]);
        $this->assertEquals(Request::STATUS[0], $request->status_label);

        $request = new Request(['status' => 1]);
        $this->assertEquals(Request::STATUS[1], $request->status_label);

        $request = new Request(['status' => 2]);
        $this->assertEquals(Request::STATUS[2], $request->status_label);
    }
}
