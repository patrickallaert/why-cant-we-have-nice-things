<?php
namespace History\Http\Controllers;

use History\Entities\Models\User;
use Twig_Environment;

class UsersController
{
    /**
     * @var Twig_Environment
     */
    protected $views;

    /**
     * @param Twig_Environment $views
     */
    public function __construct(Twig_Environment $views)
    {
        $this->views = $views;
    }

    /**
     * @return string
     */
    public function index()
    {
        $users = User::with('votes.request.votes')->get();
        $users = $users->filter(function (User $user) {
            return $user->votes->count() > 5;
        })->sortByDesc(function (User $user) {
            return $user->hivemind * -1;
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
        $user = User::with('votes.request.votes')
            ->whereName($user)
            ->firstOrFail();

        return $this->views->render('show.twig', [
            'user' => $user,
        ]);
    }
}
