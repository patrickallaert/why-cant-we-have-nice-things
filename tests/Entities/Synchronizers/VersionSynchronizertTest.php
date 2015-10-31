<?php
namespace History\Entities\Synchronizers;

use History\Entities\Models\Request;
use History\TestCase;

class VersionSynchronizertTest extends TestCase
{
    public function testCanSynchronizeVersion()
    {
        $request = Request::seed();
        $sync    = new VersionSynchronizer([
            'version'    => '1.0',
            'name'       => 'Changed stuff',
            'request_id' => $request->id,
        ]);

        $version = $sync->synchronize();
        $this->assertEquals([
            'version'    => '1.0',
            'name'       => 'Changed stuff',
            'request_id' => $request->id,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
        ], $version->toArray());
    }
}
