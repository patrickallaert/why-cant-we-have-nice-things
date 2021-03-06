<?php

namespace History\Http\Middlewares;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Interop\Container\ContainerInterface;
use League\Route\Http\Exception\NotFoundException;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Relay\MiddlewareInterface;
use Twig_Environment;
use Zend\Diactoros\Response\HtmlResponse;

class ErrorsMiddleware implements MiddlewareInterface
{
    /**
     * @var
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logs;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ErrorsMiddleware constructor.
     *
     * @param ContainerInterface $container
     * @param Twig_Environment   $twig
     * @param LoggerInterface    $logs
     */
    public function __construct(ContainerInterface $container, Twig_Environment $twig, LoggerInterface $logs)
    {
        $this->twig = $twig;
        $this->logs = $logs;
        $this->container = $container;
    }

    /**
     * @param Request                           $request  the request
     * @param Response                          $response the response
     * @param callable|MiddlewareInterface|null $next     the next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        try {
            return $next($request, $response);
        } catch (Exception $exception) {
            $this->logs->log(Logger::ERROR, $exception);

            return $next($request, $this->handleException($exception));
        }
    }

    /**
     * Handle an exception.
     *
     * @param Exception $exception
     *
     * @throws Exception
     *
     * @return Response
     */
    protected function handleException(Exception $exception)
    {
        switch (true) {
            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundException:
                $page = $this->twig->render('errors/404.twig');
                $response = new HtmlResponse($page, 404);
                break;

            default:
                if (!$this->container->get('debug')) {
                    throw $exception;
                }

                $page = $this->twig->render('errors/503.twig');
                $response = new HtmlResponse($page, 404);
                break;
        }

        return $response;
    }
}
