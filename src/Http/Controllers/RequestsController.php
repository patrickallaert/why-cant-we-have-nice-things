<?php

namespace History\Http\Controllers;

use History\Collection;
use History\Entities\Models\Request;
use Psr\Http\Message\ServerRequestInterface;

class RequestsController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $requests = Request::with('votes', 'versions')->latest()->get();

        // Compute % of passed RFCs
        $voted = $requests->filter(function (Request $request) {
            return $request->votes->count();
        });

        $passed = $voted->filter(function (Request $request) {
            return $request->status === Request::APPROVED;
        });

        return $this->render('requests/index.twig', [
            'requests' => $requests,
            'voted' => $voted,
            'passed' => $passed->count() / $voted->count(),
        ]);
    }

    /**
     * @param Request                $request
     * @param ServerRequestInterface $serverRequest
     *
     * @return string
     */
    public function show(Request $request, ServerRequestInterface $serverRequest)
    {
        $request->load('questions.votes.question', 'questions.votes.user');
        $comments = new Collection();
        if ($request->thread) {
            $comments = $this->paginate($request->thread->rootComments(), $serverRequest, 25);
        }

        return $this->render('requests/show.twig', [
            'request' => $request,
            'comments' => $comments,
        ]);
    }
}
