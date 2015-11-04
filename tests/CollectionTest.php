<?php

namespace History;

use History\Entities\Models\Request;

class CollectionTest extends TestCase
{
    public function testCanComputeAverageOfItems()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(2, $collection->average());

        $collection = new Collection();
        $this->assertEquals(0, $collection->average());
    }

    public function testCanFilterByAttribute()
    {
        $collection = new Collection([['status' => true], ['status' => false]]);
        $collection = $collection->filterBy('status');
        $this->assertEquals([['status' => true]], $collection->toArray());

        $collection = new Collection([new Request(['status' => true]), new Request(['status' => false])]);
        $collection = $collection->filterBy('status');
        $this->assertEquals([['status' => true]], $collection->toArray());
    }
}
