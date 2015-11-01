<?php
namespace History\Http\Middlewares;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr7Middlewares\Utils\FileTrait;
use Relay\MiddlewareInterface;
use Zend\Diactoros\Stream;

class ServeCachedResponse implements MiddlewareInterface
{
    use FileTrait;

    /**
     * @param Request                           $request  the request
     * @param Response                          $response the response
     * @param callable|MiddlewareInterface|null $next     the next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        // If we have a cache of this, return it
        $file = $this->getFilename($request);
        if (is_file($file)) {
            return $response->withBody(new Stream($file));
        }

        return $next($request, $response);
    }
}
