<?php

namespace History\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Fluent;
use Psr\Http\Message\ServerRequestInterface;
use Twig_Environment;
use Zend\Diactoros\Response\HtmlResponse;

abstract class AbstractController
{
    /**
     * @var Twig_Environment
     */
    protected $views;

    /**
     * @param Twig_Environment $views
     */
    public function __construct(Twig_Environment $views)
    {
        $this->views = $views;
    }

    /**
     * @param       $view
     * @param array $data
     *
     * @return HtmlResponse
     */
    protected function render($view, array $data = [])
    {
        return new HtmlResponse($this->views->render($view, $data));
    }

    /**
     * @param Builder                $query
     * @param ServerRequestInterface $request
     * @param int                    $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginate($query, ServerRequestInterface $request, $perPage = 50)
    {
        /* @var Paginator $paginator */
        $parameters = new Fluent($request->getQueryParams());
        $paginator = $query->paginate($perPage, ['*'], 'page', $parameters->page);
        $paginator->setPath($request->getUri()->getPath());

        return $paginator;
    }
}
