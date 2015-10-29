<?php
namespace History\Http\Controllers;

use History\Entities\Models\Request;
use History\Entities\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsersController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $creators = User::has('requests')->with('approvedRequests', 'requests')->get();
        $voters   = User::has('votes', '>', 5)->with('requests')->orderBy('hivemind', 'ASC')->get();

        // Sort results
        $creators = $creators->sortByDesc(function (User $user) {
           return $user->approvedRequests->count();
        });

        $voters = $voters->sortBy(function (User $user) {
           return $user->hivemind * $user->total_votes;
        });

        return $this->views->render('users/index.twig', [
            'creators' => $creators,
            'voters'   => $voters,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array                  $parameters
     *
     * @return string
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, $parameters)
    {
        $with = ['votes.question.request', 'votes.question.votes', 'requests.versions', 'requests.comments', 'requests.votes'];
        $user = User::with($with)->findOrFail($parameters['user']);

        return $this->views->render('users/show.twig', [
            'user' => $user,
        ]);
    }
}
