<?php
namespace History\Http\Controllers;

use History\Entities\Models\Vote;

class VotesController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $votes = Vote::with('user', 'question.request')
            ->latest()
            ->limit(50)
            ->get();

        return $this->views->render('votes/index.twig', [
            'votes' => $votes,
        ]);
    }
}
