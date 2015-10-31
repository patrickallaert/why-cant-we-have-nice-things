<?php
namespace History\Http\Middlewares;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Relay\MiddlewareInterface;
use Twig_Environment;
use Whoops\Run;
use Zend\Diactoros\Response\HtmlResponse;

class ErrorsMiddleware implements MiddlewareInterface
{
    /**
     * @var
     */
    private $twig;

    /**
     * ErrorsMiddleware constructor.
     *
     * @param Run              $whoops
     * @param Twig_Environment $twig
     */
    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
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
                $page     = $this->twig->render('errors/404.twig');
                $response = new HtmlResponse($page, 404);
                break;

            default:
                throw $exception;
        }

        return $response;
    }
}
