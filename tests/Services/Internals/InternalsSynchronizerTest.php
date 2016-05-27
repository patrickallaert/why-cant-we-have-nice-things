<?php

namespace History\Services\Internals;

use Carbon\Carbon;
use History\Entities\Models\Request;
use History\Entities\Models\Threads\Group;
use History\Entities\Models\Threads\Thread;
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
                'subject' => 'foobar',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'date' => '2011-01-01 01:01:01',
            ],
            [
                'xref' => 'php.internals:2321322',
                'number' => 3,
                'subject' => 'foobar',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'date' => '2011-01-01 01:01:01',
            ],
            [
                'xref' => 'php.internals:2321324',
                'number' => 1,
                'subject' => 'foobar',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'date' => '2011-01-01 01:01:01',
            ],
        ], 3);

        $article = $created[2];
        $this->assertEqualsPartially([
            'xref' => 'php.internals:2321321',
            'contents' => 'foobar',
            'thread_id' => $thread->id,
            'comment_id' => null,
            'user_id' => $user->id,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
            'id' => $article['id'],
        ], $article->toArray());
        $this->assertEquals($request->id, $article->thread->request->id);
    }

    public function testIsAbleToMatchRequestsEventIfTitleIsntIdentical()
    {
        $threadName = 'RE: [RFC][DISCUSSION]: Trailing commas in all list syntax';

        $request = Request::firstOrCreate(['name' => 'Trailing Commas In List Syntax']);
        $thread = Thread::seed(['name' => $threadName, 'request_id' => $request->id]);
        $user = User::create(['full_name' => 'Maxime Fabre']);
        $created = $this->mockSynchronization([
            ['xref' => 1, 'subject' => 'foobar'],
            [
                'xref' => 'php.internals:2321321',
                'number' => 2,
                'subject' => $threadName,
                'from' => 'Maxime Fabre (foo@bar.com)',
                'date' => '2011-01-01 01:01:01',
            ],
            [
                'xref' => 'php.internals:2321322',
                'number' => 3,
                'subject' => 'Re: Re: Make sessions use php_random_bytes in 7.1',
                'from' => 'Maxime Fabre (foo@bar.com)',
                'date' => '2011-01-01 01:01:01',
            ],
        ], 2);

        $article = $created[1];
        $this->assertEqualsPartially([
            'xref' => 'php.internals:2321321',
            'contents' => 'foobar',
            'references' => [],
            'thread_id' => $thread->id,
            'comment_id' => null,
            'user_id' => $user->id,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
            'id' => $article['id'],
        ], $article->toArray());
        $this->assertEquals($request->id, $article->thread->request->id);
    }

    /**
     * @param array $messages
     * @param int   $matched
     *
     * @return array
     */
    protected function mockSynchronization(array $messages, $matched = 1)
    {
        $numberArticles = count($messages);
        Group::create(['name' => 'php.internals', 'high' => $numberArticles, 'low' => 1]);

        $internals = Mockery::mock(Internals::class);
        $internals->shouldReceive('getGroups')->once()->andReturn([
            ['name' => 'php.internals', 'high' => $numberArticles, 'low' => 1],
        ]);
        $internals->shouldReceive('setGroup')->times(1)->with('php.internals');
        $internals->shouldReceive('setGroup')->times($numberArticles)->with('php.internals', true);
        $internals->shouldReceive('findArticleFromReference')->never()->andReturn();
        $internals->shouldReceive('getArticle')->andReturnUsing(function ($i) use ($messages) {
            $message = $messages[$i - 1];
            $message['contents'] = 'foobar';
            $message['references'] = [];
            $message['date'] = new Carbon(array_get($message, 'date', new Carbon()));

            return $message;
        });

        $this->container->add(Internals::class, $internals);

        /** @var InternalsSynchronizer $sync */
        $sync = $this->container->get(InternalsSynchronizer::class);
        $sync->setAsync(false);
        $created = $sync->synchronize();
        $this->assertCount($matched, $created['php.internals']);

        return $created['php.internals'];
    }
}
