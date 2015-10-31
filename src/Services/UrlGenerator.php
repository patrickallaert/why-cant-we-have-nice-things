<?php
namespace History\Services;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use League\Route\Route;

/**
 * A disgusting URL generator for league/route.
 */
class UrlGenerator
{
    /**
     * @var Route[]
     */
    protected $routes;

    /**
     * UrlGenerator constructor.
     *
     * @param array $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @param string       $name
     * @param string|array $parameters
     *
     * @return string
     */
    public function to($name, $parameters = [])
    {
        $action = $this->routeToCallable($name);
        foreach ($this->routes as $route) {
            $path = $route->getPath();
            if ($route->getCallable() !== $action) {
                continue;
            }

            return $this->replaceParametersInPath($path, $parameters);
        }

        throw new InvalidArgumentException(sprintf('Unable to generate URL for %s (%s)', $name, $action));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function routeToCallable($name)
    {
        $callable = str_replace('.', 'Controller::', ucfirst($name));
        $callable = 'History\Http\Controllers\\'.$callable;

        return $callable;
    }

    /**
     * @param string       $path
     * @param string|array $parameters
     *
     * @return string
     */
    protected function replaceParametersInPath($path, $parameters = [])
    {
        if (!$parameters) {
            return $path;
        }

        return preg_replace_callback('/{(.+)}/', function ($pattern) use ($parameters) {
            return is_string($parameters) ? $parameters : Arr::get($parameters, $pattern[1], $pattern[1]);
        }, $path);
    }
}
