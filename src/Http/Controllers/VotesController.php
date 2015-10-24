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
                     ->paginate(
                         50,
                         ['*'],
                         'page',
                         $this->request->get('page')
                     );

        $votes->setPath('votes');

        return $this->views->render('votes/index.twig', [
            'votes' => $votes,
        ]);
    }
}
