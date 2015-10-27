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
        $creators = User::has('requests')->orderBy('success', 'DESC')->get();
        $voters   = User::has('votes', '>', 5)->with('requests')->orderBy('hivemind', 'ASC')->get();

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
        $user = User::with('votes.question.request', 'requests')->findOrFail($parameters['user']);

        return $this->views->render('users/show.twig', [
            'user' => $user,
        ]);
    }
}
