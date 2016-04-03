<?php

namespace History\Http;

use History\Entities\Models\Company;
use History\Entities\Models\Request;
use History\Entities\Models\Threads\Group;
use History\Entities\Models\Threads\Thread;
use History\Entities\Models\User;
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
        $models = [
            'group' => Group::class,
            'thread' => Thread::class,
            'company' => Company::class,
            'request' => Request::class,
            'user' => User::class,
        ];

        foreach ($vars as $key => $value) {
            $model = array_get($models, $key);
            if (class_exists($model)) {
                $vars[$key] = $model::where(['slug' => $value])->firstOrFail();
            }
        }

        return parent::dispatch($controller, $vars, $route);
    }
}
