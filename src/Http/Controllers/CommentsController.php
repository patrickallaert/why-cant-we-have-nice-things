<?php
namespace History\Http\Controllers;

use History\Entities\Models\Comment;

class CommentsController extends AbstractController
{
    /**
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function index()
    {
        $comments = Comment::latest()->get();

        return $this->render('comments/index.twig', [
            'comments' => $comments,
        ]);
    }
}
