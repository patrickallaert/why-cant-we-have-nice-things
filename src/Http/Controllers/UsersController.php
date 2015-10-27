<?php
namespace History\Http\Controllers;

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
        $users = User::with('requests')->get();
        $users = $users->filter(function (User $user) {
            return $user->total_votes > 5 || $user->requests->count();
        })->sortBy(function (User $user) {
            return $user->hivemind;
        });

        return $this->views->render('users/index.twig', [
            'users' => $users,
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
        $user = User::with('votes.question.request', 'requests')->findOrFail($parameters['user']);

        return $this->views->render('users/show.twig', [
            'user' => $user,
        ]);
    }
}
