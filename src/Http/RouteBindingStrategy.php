<?php

namespace History\Http;

use League\Route\Route;
use League\Route\Strategy\ParamStrategy;

class RouteBindingStrategy extends ParamStrategy
{
    /**
     * @param callable   $controller
     * @param array      $vars
     * @param Route|null $route
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(callable $controller, array $vars, Route $route = null)
    {
        foreach ($vars as $key => $value) {
            $model = sprintf('History\Entities\Models\%s', ucfirst($key));
            if (class_exists($model)) {
                $vars[$key] = $model::where(['slug' => $value])->firstOrFail();
            }
        }

        return parent::dispatch($controller, $vars, $route);
    }
}
