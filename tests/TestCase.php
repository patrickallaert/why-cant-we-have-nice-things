<?php
namespace History;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Capsule\Manager;
use League\Container\Container;
use League\Container\ContainerInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $app;

    public function setUp()
    {
        // Create app
        $container = new Container();
        $app       = new Application($container);
        $this->app = $app->getContainer();

        // Mock current time
        Carbon::setTestNow(new Carbon('2011-01-01 01:01:01'));

        Manager::beginTransaction();
    }

    public function tearDown()
    {
        Mockery::close();
        Manager::rollback();
    }

    /**
     * Get a dummy HTML page.
     *
     * @param string $page
     *
     * @return string
     */
    protected function getDummyPage($page)
    {
        return file_get_contents(__DIR__.'/_pages/'.$page.'.html');
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// MOCKS ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param array $cached
     *
     * @return MockInterface
     */
    protected function mockCache(array $cached = [])
    {
        $cache = Mockery::mock(Repository::class);
        $cache->shouldReceive('rememberForever')->andReturnUsing(function ($key, $callback) use ($cached) {
            if (array_key_exists($key, $cached)) {
                return $cached[$key];
            }

            return $callback();
        });

        return $cache;
    }
}
