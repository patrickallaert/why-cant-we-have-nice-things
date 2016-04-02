<?php
namespace History\CommandsBus\Commands;

use Carbon\Carbon;
use DateTime;
use History\Application;
use History\CommandBus\Commands\FetchMetadataCommand;
use History\CommandBus\Commands\FetchMetadataHandler;
use History\Entities\Models\User;
use History\Services\Github\Github;
use History\TestCase;
use Illuminate\Database\Capsule\Manager;
use Mockery;

class FetchMetadataHandlerTest extends TestCase
{
    public function testCanFetchUserMetadata()
    {
        $user = User::create([
            'name' => 'anahkiasen',
        ]);

        $command = new FetchMetadataCommand($user);
        $handler = new FetchMetadataHandler($this->getGithubMock($user));

        $user = $handler->handle($command);

        $this->assertEquals('anahkiasen', $user->name);
        $this->assertEquals('Madewithlove', $user->company->name);
        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertEquals('foo.com/bar', $user->github_avatar);
        $this->assertEquals('Maxime Fabre', $user->full_name);
        $this->assertEquals('Anahkiasen', $user->github_id);
        $this->assertInstanceOf(DateTime::class, $user->refreshed_at);
    }

    /**
     * @dataProvider provideUsers
     *
     * @param User $user
     * @param bool $refreshed
     */
    public function testCanDecideWhetherToUpdateUser(User $user, bool $refreshed)
    {
        $user->save();

        $command = new FetchMetadataCommand($user);
        $handler = new FetchMetadataHandler($this->getGithubMock($user));

        $user = $handler->handle($command);
        $method = $refreshed ? 'assertNotNull' : 'assertNull';
        $this->$method($user);
    }

    /**
     * @return array
     */
    public function provideUsers()
    {
        new Application();

        $existing = new User();
        $existing->github_id = 1;
        $existing->github_avatar = 'foobar';

        $refreshed = new User();
        $refreshed->github_id = 1;
        $refreshed->github_avatar = 'foobar';
        $refreshed->refreshed_at = Carbon::now()->subDays(35);

        return [
            [new User(), true],
            [$existing, false],
            [$refreshed, true],
        ];
    }

    /**
     * @param User $user
     *
     * @return Mockery\MockInterface
     */
    private function getGithubMock(User $user)
    {
        $github = Mockery::mock(Github::class);
        $github->shouldReceive('searchUser')->with($user)->andReturn([
            'total_count' => 2,
            'items' => [
                ['login' => 'Anahkiasen'],
                ['login' => 'foobar'],
            ],
        ]);
        $github->shouldReceive('getUserInformations')->with('Anahkiasen')->andReturn([
            'email' => 'foo@bar.com',
            'full_name' => 'Maxime Fabre',
            'avatar_url' => 'foo.com/bar',
            'company' => 'Madewithlove',
        ]);

        return $github;
    }
}
