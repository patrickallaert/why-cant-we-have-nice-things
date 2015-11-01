<?php
namespace History\Http\Controllers;

use History\Entities\Models\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestsController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $requests = Request::with('versions', 'comments', 'votes')->latest()->get();

        // Compute % of passed RFCs
        $voted = $requests->filter(function (Request $request) {
            return $request->votes->count();
        });

        $passed = $requests->filter(function (Request $request) {
            return $request->status === 2;
        });

        return $this->render('requests/index.twig', [
            'requests' => $requests,
            'voted'    => $voted,
            'passed'   => $passed->count() / $requests->count(),
        ]);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @param ResponseInterface      $response
     * @param array                  $parameters
     *
     * @return string
     */
    public function show(ServerRequestInterface $serverRequest, ResponseInterface $response, $parameters)
    {
        $request  = Request::with('questions.votes.question', 'questions.votes.user')->where('slug', $parameters['request'])->firstOrFail();
        $comments = $this->paginate($request->rootComments(), $serverRequest, 25);

        return $this->render('requests/show.twig', [
            'request'  => $request,
            'comments' => $comments,
        ]);
    }
}
