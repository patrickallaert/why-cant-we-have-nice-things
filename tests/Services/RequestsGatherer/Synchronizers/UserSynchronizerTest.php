<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Entities\Models\User;
use History\TestCase;

class UserSynchronizerTest extends TestCase
{
    public function testCanSynchronizerUser()
    {
        $sync = new UserSynchronizer([
            'username'      => 'foobar',
            'email'         => 'foo@bar.com',
            'full_name'     => 'Foo Bar',
            'contributions' => ['foo', 'bar'],
        ]);

        $user = $sync->synchronize();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals([
            'name'          => 'foobar',
            'email'         => 'foo@bar.com',
            'full_name'     => 'Foo Bar',
            'contributions' => ['foo', 'bar'],
        ], $user->toArray());
    }

    public function testCanRetrieveExistingUser()
    {
        $existing = User::create(['email' => 'foo@bar.com']);
        $sync     = new UserSynchronizer(['email' => 'foo@bar.com']);
        $user     = $sync->synchronize();
        $this->assertEquals($existing->id, $user->id);

        $existing = User::create(['name' => 'foobar']);
        $sync     = new UserSynchronizer(['username' => 'foobar']);
        $user     = $sync->synchronize();
        $this->assertEquals($existing->id, $user->id);
    }

    public function testCanInfereUsernameFromEmail()
    {
        $existing = User::create(['name' => 'foobarz']);
        $sync     = new UserSynchronizer(['email' => 'foobarz@php.net']);
        $user     = $sync->synchronize();
        $this->assertEquals($existing->id, $user->id);
    }
}
