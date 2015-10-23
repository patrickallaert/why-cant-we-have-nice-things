<?php
namespace History\Http\Controllers;

use History\Entities\User;
use History\RequestsGatherer\RequestsGatherer;
use Twig_Environment;

class UsersController
{
    /**
     * @var Twig_Environment
     */
    protected $views;
    /**
     * @var RequestsGatherer
     */
    private $requestsGatherer;

    /**
     * @param Twig_Environment $views
     * @param RequestsGatherer $requestsGatherer
     */
    public function __construct(Twig_Environment $views, RequestsGatherer $requestsGatherer)
    {
        $this->views            = $views;
        $this->requestsGatherer = $requestsGatherer;
    }

    /**
     * @return string
     */
    public function index()
    {
        $users = $this->requestsGatherer->getUserVotes();
        $users = $users->filter(function (User $user) {
            return $user->total > 5;
        })->sortByDesc(function (User $user) {
            return $user->approval;
        });

        return $this->views->render('index.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @param string $user
     *
     * @return string
     */
    public function show($user)
    {
        $users = $this->requestsGatherer->getUserVotes();
        $user  = $users->get($user);

        return $this->views->render('show.twig', [
            'user' => $user,
        ]);
    }
}
