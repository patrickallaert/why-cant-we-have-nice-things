<?php
namespace History\Entities\Synchronizers;

use History\Entities\Models\User;
use History\TestCase;

class UserSynchronizerTest extends TestCase
{
    public function testCanSynchronizerUser()
    {
        $sync = new UserSynchronizer([
            'name'          => 'foobar',
            'email'         => 'foo@bar.com',
            'full_name'     => 'Foo Bar',
            'contributions' => ['foo', 'bar'],
            'company'       => 'Zend',
        ]);

        $user = $sync->synchronize();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals([
            'name'          => 'foobar',
            'email'         => 'foo@bar.com',
            'full_name'     => 'Foo Bar',
            'contributions' => ['foo', 'bar'],
            'company'       => 'Zend',
            'github_avatar' => null,
        ], $user->toArray());
    }

    public function testCanRetrieveExistingUser()
    {
        $existing = User::create(['email' => 'foo@bar.com']);
        $sync     = new UserSynchronizer(['email' => 'foo@bar.com']);
        $user     = $sync->synchronize();
        $this->assertEquals($existing->id, $user->id);

        $existing = User::create(['name' => 'foobar']);
        $sync     = new UserSynchronizer(['name' => 'foobar']);
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

    public function testDoesntOverwriteInformationsWithLessPertinentOnes()
    {
        $existing = User::create(['name' => 'foo', 'email' => 'foo@php.net', 'full_name' => 'Marco']);
        $sync     = new UserSynchronizer(['full_name' => 'Marco', 'email' => 'foobarz@gmail.com']);
        $user     = $sync->synchronize();

        $this->assertEquals($existing->id, $user->id);
        $this->assertEquals('foobarz@gmail.com', $user->email);
        $this->assertEquals('foo', $user->name);
        $this->assertEquals('Marco', $user->full_name);
    }

    public function testNeverOverwriteWithPhpEmail()
    {
        $existing = User::create(['name' => 'foo', 'email' => 'foo@gmail.com']);
        $sync     = new UserSynchronizer(['name' => 'foo', 'email' => 'foo@php.net']);
        $user     = $sync->synchronize();

        $this->assertEquals($existing->id, $user->id);
        $this->assertEquals('foo@gmail.com', $user->email);
    }

    public function testDoesntUnifyBlankProfiles()
    {
        $existing = User::create(['name' => 'foobar', 'email' => '', 'full_name' => '']);
        $sync     = new UserSynchronizer(['name' => 'foo', 'email' => '', 'full_name' => '']);
        $user     = $sync->synchronize();

        $this->assertNotEquals($existing->id, $user->id);
    }

    public function testKnowsHowToReassignFullnames()
    {
        $sync = new UserSynchronizer(['name' => 'Maxime Fabre']);
        $user = $sync->synchronize();

        $this->assertEquals('Maxime Fabre', $user->full_name);
        $this->assertNull($user->name);
    }
}
