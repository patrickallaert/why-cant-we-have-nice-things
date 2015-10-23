<?php
namespace History\Http\Controllers;

use Twig_Environment;

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
}
