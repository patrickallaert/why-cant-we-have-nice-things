<?php
namespace History\Http\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

abstract class AbstractController
{
    /**
     * @var Twig_Environment
     */
    protected $views;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Twig_Environment $views
     * @param Request          $request
     */
    public function __construct(Twig_Environment $views, Request $request)
    {
        $this->views = $views;
        $this->request = $request;
    }
}
