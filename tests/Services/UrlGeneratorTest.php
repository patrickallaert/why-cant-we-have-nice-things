<?php

namespace History\Services;

use History\TestCase;
use InvalidArgumentException;
use League\Route\RouteCollection;

class UrlGeneratorTest extends TestCase
{
    /**
     * @var UrlGenerator
     */
    protected $generator;

    public function setUp()
    {
        parent::setUp();

        $routes = new RouteCollection();
        $urls = [
            $routes->get('users', 'History\Http\Controllers\FooController::index'),
            $routes->get('users/{user}', 'History\Http\Controllers\FooController::show'),
            $routes->get('users/{foo}/bar/{baz}', 'History\Http\Controllers\FooController::bar'),
        ];

        $this->generator = new UrlGenerator($urls);
    }

    /**
     * @dataProvider provideUrls
     *
     * @param string       $route
     * @param string|array $parameters
     * @param string       $expected
     */
    public function testCanGeneratorUrlToRoute($route, $parameters, $expected)
    {
        $this->assertEquals($expected, $this->generator->to($route, $parameters));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowsExceptionOnInvalidRoute()
    {
        $this->generator->to('foo.sdfsdf');
    }

    /**
     * @return array
     */
    public function provideUrls()
    {
        return [
            ['foo.index', [], '/users'],
            ['foo.show', ['user' => 'foobar'], '/users/foobar'],
            ['foo.show', 'foobar', '/users/foobar'],
            ['foo.show', 12, '/users/12'],
            ['foo.show', ['foobar' => 'foobar'], '/users/user'],
            ['foo.bar', ['foo' => 'a', 'baz' => 'b'], '/users/a/bar/b'],
        ];
    }
}
