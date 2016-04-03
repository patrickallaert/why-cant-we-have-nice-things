<?php

namespace History\Http\Controllers;

use History\Entities\Models\Threads\Group;
use History\Entities\Models\Threads\Thread;
use Psr\Http\Message\ServerRequestInterface;

class ThreadsController extends AbstractController
{
    /**
     * @param Group                  $group
     * @param Thread                 $thread
     * @param ServerRequestInterface $request
     *
     * @return Thread
     */
    public function show(Group $group, Thread $thread, ServerRequestInterface $request)
    {
        $comments = $this->paginate($thread->rootComments(), $request, 25);

        return $this->render('threads/show.twig', [
            'group' => $group,
            'thread' => $thread,
            'comments' => $comments,
        ]);
    }
}
