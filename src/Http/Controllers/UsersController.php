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
        $users = User::with('requests')->get();
        $users = $users->filter(function (User $user) {
            return $user->total_votes > 5;
        })->sortBy(function (User $user) {
            return $user->hivemind;
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
        $user = User::with('votes.question.request', 'requests')
                    ->whereName($user)
                    ->firstOrFail();

        return $this->views->render('users/show.twig', [
            'user' => $user,
        ]);
    }
}
