<?php
namespace History;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use League\Container\Container;
use League\Container\ContainerInterface;
use Mockery;
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
}
