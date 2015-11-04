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

    public function testFiresEventOnStatusChange()
    {
        $request         = Request::seed(['status' => 1, 'created_at' => '2010-01-01']);
        $request->status = 2;
        $request->save();

        $event = $request->events->first();
        $this->assertEquals([
            'id'             => $event->id,
            'type'           => 'rfc_status',
            'eventable_id'   => $request->id,
            'eventable_type' => 'History\Entities\Models\Request',
            'metadata'       => ['new_status' => 2],
            'created_at'     => '2011-01-01 01:01:01',
            'updated_at'     => '2011-01-01 01:01:01',
        ], $event->toArray());
    }
}
