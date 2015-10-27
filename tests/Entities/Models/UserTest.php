<?php
namespace History\Entities\Models;

use History\TestCase;

class UserTest extends TestCase
{
    public function testCanFindNameToDisplayForUser()
    {
        $user = new User(['name' => 'foobar']);
        $this->assertEquals('foobar', $user->display_name);

        $user = new User(['name' => '', 'full_name' => 'Foo Bar']);
        $this->assertEquals('Foo Bar', $user->display_name);

        $user = new User(['name' => '', 'full_name' => '', 'email' => 'foo@bar']);
        $this->assertEquals('foo@bar', $user->display_name);
    }

    public function testCanComputeNegativenessOfUser()
    {
        $user = new User(['no_votes' => 2, 'total_votes' => 4]);
        $this->assertEquals(0.5, $user->negativeness);
    }
}
