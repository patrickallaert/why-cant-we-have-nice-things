<?php

namespace History\Http\Controllers;

use History\Collection;
use History\Entities\Models\User;
use History\Services\Graphs\GraphicsGenerator;

class UsersController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        /** @var Collection $creators */
        /* @var Collection $voters */
        $creators = User::has('requests')->with('approvedRequests', 'requests')->get();
        $voters = User::has('votes', '>', 5)->with('requests')->orderBy('hivemind', 'ASC')->get();

        // Sort results
        $creators = $creators->sortByDesc(function (User $user) {
            return $user->approvedRequests->count();
        });

        $voters = $voters->sortByDesc(function (User $user) {
            return $user->total_votes;
        });

        return $this->render('users/index.twig', [
            'creators' => $creators,
            'voters' => $voters,
        ]);
    }

    /**
     * @param User $user
     *
     * @return string
     */
    public function show(User $user)
    {
        $with = [
            'votes.question.request',
            'votes.question.votes',
            'requests.versions',
            'requests.comments',
            'requests.votes',
        ];

        return $this->render('users/show.twig', [
            'user' => $user->load($with),
            'chart' => (new GraphicsGenerator())->computePositiveness($user),
        ]);
    }
}
