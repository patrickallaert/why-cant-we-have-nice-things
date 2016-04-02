<?php

namespace History\Services;

use History\Entities\Models\User;
use History\Services\Github\Github;
use History\TestCase;

class GithubTest extends TestCase
{
    public function testCanSearchForUserDependingOnExistingInformations()
    {
        $user = new User(['full_name' => 'foobar', 'name' => 'foobar']);
        $results = [
            'total_count' => 1,
            [
                ['name' => 'lol'],
            ],
        ];

        $this->mockCache([
            'github:search:foobar' => $results,
        ]);

        /** @var Github $github */
        $github = $this->container->get(Github::class);
        $informations = $github->searchUser($user);

        $this->assertEquals($results, $informations);
    }

    public function testCancelsIfNoAnnouncedResults()
    {
        $user = new User(['full_name' => 'foobar', 'name' => 'foobar']);

        $this->mockCache([
            'github:search:foobar' => ['total_count' => 0],
        ]);

        /** @var Github $github */
        $github = $this->container->get(Github::class);
        $informations = $github->searchUser($user);

        $this->assertNull($informations);
    }
}
