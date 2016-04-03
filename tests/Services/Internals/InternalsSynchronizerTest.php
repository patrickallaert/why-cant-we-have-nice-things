<?php

namespace History\Services\Internals;

use History\Entities\Models\Request;
use History\Entities\Models\Thread;
use History\Entities\Models\User;
use History\TestCase;
use Mockery;

class InternalsSynchronizerTest extends TestCase
{
    public function testCanFetchArticles()
    {
        $request = Request::create(['name' => 'foobar']);
        $thread = Thread::seed(['name' => 'foobar', 'request_id' => $request->id]);
        $user = User::create(['full_name' => 'Maxime Fabre']);
        $created = $this->mockSynchronization([
            ['xref' => 1, 'subject' => 'foobar'],
            [
                'xref' => 'php.internals:2321321',
                'number' => 2,
                'subject' => '[VOTE] foobar RFC',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date' => '2011-01-01 01:01:01',
            ],
            [
                'xref' => 'php.internals:2321322',
                'number' => 3,
                'subject' => 'foobar RFC',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date' => '2011-01-01 01:01:01',
            ],
            [
                'xref' => 'php.internals:2321324',
                'number' => 1,
                'subject' => '[RFC] foobar',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date' => '2011-01-01 01:01:01',
            ],
        ], 3);

        $this->assertEquals([
            'xref' => 'php.internals:2321321',
            'name' => 'foobar',
            'contents' => 'foobar',
            'thread_id' => $thread->id,
            'comment_id' => null,
            'user_id' => $user->id,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
            'id' => $created[0]['id'],
        ], $created[0]->toArray());
        $this->assertEquals($request->id, $created[0]->thread->request->id);
    }

    public function testIsAbleToMatchRequestsEventIfTitleIsntIdentical()
    {
        $request = Request::firstOrCreate(['name' => 'Trailing Commas In List Syntax']);
        $thread = Thread::seed(['name' => 'Trailing commas in all list syntax', 'request_id' => $request->id]);
        $user = User::create(['full_name' => 'Maxime Fabre']);
        $created = $this->mockSynchronization([
            ['xref' => 1, 'subject' => 'foobar'],
            [
                'xref' => 'php.internals:2321321',
                'number' => 2,
                'subject' => 'RE: [RFC][DISCUSSION]: Trailing commas in all list syntax',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date' => '2011-01-01 01:01:01',
            ],
            [
                'xref' => 'php.internals:2321322',
                'number' => 3,
                'subject' => 'Re: Re: Make sessions use php_random_bytes in 7.1',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date' => '2011-01-01 01:01:01',
            ],
        ], 1);

        $this->assertEquals([
            'xref' => 'php.internals:2321321',
            'name' => 'Trailing commas in all list syntax',
            'contents' => 'foobar',
            'thread_id' => $thread->id,
            'comment_id' => null,
            'user_id' => $user->id,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
            'id' => $created[0]['id'],
        ], $created[0]->toArray());
        $this->assertEquals($request->id, $created[0]->thread->request->id);
    }

    /**
     * @param array $messages
     * @param int   $matched
     *
     * @return array
     */
    protected function mockSynchronization(array $messages, $matched = 1)
    {
        $lastArticle = 40000;
        $numberArticles = ($lastArticle - 40000) / InternalsSynchronizer::CHUNK;
        $internals = Mockery::mock(Internals::class);
        $internals->shouldReceive('getTotalNumberArticles')->once()->andReturn($lastArticle);
        $internals->shouldReceive('getArticleBody')->times($matched)->andReturn('foobar');
        $internals->shouldReceive('findArticleFromReference')->never()->andReturn();
        $internals->shouldReceive('getArticles')->times($numberArticles + 1)->andReturn($messages);

        $this->container->add(Internals::class, $internals);

        $sync = $this->container->get(InternalsSynchronizer::class);
        $created = $sync->synchronize();
        $this->assertCount($matched, $created);

        return $created;
    }
}
