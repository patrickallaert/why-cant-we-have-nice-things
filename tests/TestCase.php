<?php
namespace History;

use Carbon\Carbon;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use Illuminate\Database\Capsule\Manager;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\FactoryMuffin\Facade;
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

        // Load FM factories
        Facade::loadFactories(__DIR__.'/../resources/factories');

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
