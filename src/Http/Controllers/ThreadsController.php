<?php

namespace History\Http\Controllers;

use History\Entities\Models\Threads\Thread;
use Psr\Http\Message\ServerRequestInterface;

class ThreadsController extends AbstractController
{
    /**
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function index()
    {
        $threads = Thread::latest()->get();

        return $this->render('threads/index.twig', [
            'threads' => $threads,
        ]);
    }

    /**
     * @param Thread                 $thread
     * @param ServerRequestInterface $request
     *
     * @return Thread
     */
    public function show(Thread $thread, ServerRequestInterface $request)
    {
        $comments = $this->paginate($thread->rootComments(), $request, 25);

        return $this->render('threads/show.twig', [
            'thread' => $thread,
            'comments' => $comments,
        ]);
    }
}
