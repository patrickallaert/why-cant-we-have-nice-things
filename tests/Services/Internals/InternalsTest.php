<?php
namespace History\Services\Internals;

use History\TestCase;
use Mockery;
use Mockery\MockInterface;
use Rvdv\Nntp\ClientInterface;
use Rvdv\Nntp\Command\XoverCommand;

class InternalsTest extends TestCase
{
    public function testOnlyConnectsWhenNeeded()
    {
        $internals = $this->mockInternals();

        $total = $internals->getTotalNumberArticles();
        $total = $internals->getTotalNumberArticles();

        $this->assertEquals(25, $total);
    }

    public function testCanGetArticles()
    {
        $internals = $this->mockInternals(function (MockInterface $client) {
            $command = Mockery::mock(XoverCommand::class, [
                'getResult' => [['foo'], ['bar']],
            ]);

            $client->shouldReceive('overviewFormat->getResult')->andReturn(['subject' => 'foobar']);
            $client->shouldReceive('xover')->once()->with(1, 5, ['subject' => 'foobar'])->andReturn($command);
        });

        $this->assertEquals([['foo'], ['bar']], $internals->getArticles(1, 5));
    }

    /**
     * @param callable $callback
     *
     * @return Internals
     */
    private function mockInternals(callable $callback = null)
    {
        $cache = $this->mockCache();

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('connect')->once();
        $client->shouldReceive('group->getResult')->andReturn(['count' => 25]);
        if ($callback) {
            $callback($client);
        }

        return new Internals($cache, $client);
    }
}
