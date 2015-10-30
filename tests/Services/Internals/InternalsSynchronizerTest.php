<?php
namespace History\Services\Internals;

use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\TestCase;
use Mockery;

class InternalsSynchronizerTest extends TestCase
{
    public function testCanFetchArticles()
    {
        $lastArticle    = 40000;
        $numberArticles = ($lastArticle - 40000) / InternalsSynchronizer::CHUNK;

        $request = Request::create(['name' => 'foobar']);
        $user    = User::create(['full_name' => 'Maxime Fabre']);

        $internals = Mockery::mock(Internals::class);
        $internals->shouldReceive('getTotalNumberArticles')->once()->andReturn($lastArticle);
        $internals->shouldReceive('getArticleBody')->times(3)->andReturn('foobar');
        $internals->shouldReceive('findArticleFromReference')->times(3)->andReturn();
        $internals->shouldReceive('getArticles')->times($numberArticles + 1)->andReturn([
            ['xref' => 1, 'subject' => 'foobar'],
            [
                'xref'       => 'php.internals:2321321',
                'number'     => 2,
                'subject'    => '[VOTE] foobar RFC',
                'from'       => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date'       => '2011-01-01 01:01:01',
            ],
            [
                'xref'       => 'php.internals:2321322',
                'number'     => 3,
                'subject'    => 'foobar RFC',
                'from'       => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date'       => '2011-01-01 01:01:01',
            ],
            [
                'xref'       => 'php.internals:2321324',
                'number'     => 1,
                'subject'    => '[RFC] foobar',
                'from'       => 'Maxime Fabre (foo@bar.com)',
                'references' => '',
                'date'       => '2011-01-01 01:01:01',
            ],
        ]);

        $sync    = new InternalsSynchronizer($internals);
        $created = $sync->synchronize();

        $this->assertCount(3, $created);
        $this->assertEquals([
            'xref'       => 'php.internals:2321321',
            'name'       => 'foobar',
            'contents'   => 'foobar',
            'request_id' => $request->id,
            'comment_id' => null,
            'user_id'    => $user->id,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
            'id'         => $created[0]['id'],
        ], $created[0]->toArray());
    }
}
