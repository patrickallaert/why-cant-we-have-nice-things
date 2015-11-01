<?php
namespace History\Http\Controllers;

class PagesController extends AbstractController
{
    /**
     * @return string
     */
    public function about()
    {
        return $this->render('about.twig');
    }
}
