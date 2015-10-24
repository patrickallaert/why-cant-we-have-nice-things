<?php
namespace History;

class CollectionTest extends TestCase
{
    public function testCanComputeAverageOfItems()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(2, $collection->average());

        $collection = new Collection();
        $this->assertEquals(0, $collection->average());
    }
}
