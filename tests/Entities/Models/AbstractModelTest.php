<?php

namespace History\Entities\Models;

use History\TestCase;

class AbstractModelTest extends TestCase
{
    public function testCanGetIdentifier()
    {
        $model     = new User();
        $model->id = 1;
        $this->assertEquals(1, $model->identifier);

        $model->slug = 'foobar';
        $this->assertEquals('foobar', $model->identifier);
    }
}
