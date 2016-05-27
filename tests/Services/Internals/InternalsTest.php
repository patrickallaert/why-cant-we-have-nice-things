<?php

namespace History\Services\Internals;

use History\TestCase;
use Mockery;
use Mockery\MockInterface;
use Rvdv\Nntp\ClientInterface;
use Rvdv\Nntp\Command\ArticleCommand;
use Rvdv\Nntp\Command\XoverCommand;

class InternalsTest extends TestCase
{
    public function testOnlyConnectsWhenNeeded()
    {
        $internals = $this->mockInternals();

        $internals->getGroups();
        $internals->getGroups();
    }

    public function testCanGetArticles()
    {
        $internals = $this->mockInternals(function (MockInterface $client) {
            $command = Mockery::mock(ArticleCommand::class, [
                'getResult' => 'foo',
            ]);

            $client->shouldReceive('sendCommand')->andReturn($command);
        });

        $this->assertEquals(['contents' => 'foo'], $internals->getArticle(1));
    }

    /**
     * @param callable $callback
     *
     * @return Internals
     */
    private function mockInternals(callable $callback = null)
    {
        $cache = $this->mockCache();

        $parser = Mockery::mock(ArticleParser::class);
        $parser->shouldReceive('parse')->with('foo')->andReturn(['contents' => 'foo']);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('connect')->once();
        $client->shouldReceive('listGroups->getResult')->andReturn(['php.internals']);
        $client->shouldReceive('group->getResult')->andReturn(['count' => 25]);
        if ($callback) {
            $callback($client);
        }

        return new Internals($cache, $client, $parser);
    }
}
