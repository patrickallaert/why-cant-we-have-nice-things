<?php
namespace History\Entities\Models;

use History\TestCase;

class RequestTest extends TestCase
{
    public function testCanGetRequestLabel()
    {
        $request = new Request(['status' => 0]);
        $this->assertEquals('Declined', $request->status_label);

        $request = new Request(['status' => 1]);
        $this->assertEquals('Draft', $request->status_label);

        $request = new Request(['status' => 2]);
        $this->assertEquals('Implemented', $request->status_label);
    }
}
