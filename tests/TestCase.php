<?php
namespace History;

use League\Container\Container;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $app;

    public function setUp()
    {
        $container = new Container();
        $this->app = new Application($container);
    }
}
