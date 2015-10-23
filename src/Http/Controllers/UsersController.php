<?php
namespace History\Http\Controllers;

use History\Entities\Models\User;

class UsersController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $users = User::with('votes.request')->get();
        $users = $users->filter(function (User $user) {
            return $user->votes->count() > 5;
        })->sortByDesc(function (User $user) {
            return $user->hivemind * -1;
        });

        return $this->views->render('users/index.twig', [
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

        return $this->views->render('users/show.twig', [
            'user' => $user,
        ]);
    }
}
