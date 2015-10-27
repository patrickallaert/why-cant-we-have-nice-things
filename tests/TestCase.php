<?php
namespace History;

use Illuminate\Database\Capsule\Manager;
use League\Container\Container;
use League\Container\ContainerInterface;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $app;

    public function setUp()
    {
        $container = new Container();
        $app       = new Application($container);

        $this->app = $app->getContainer();

        Manager::beginTransaction();
    }

    public function tearDown()
    {
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
