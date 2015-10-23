<?php
namespace History\Http\Controllers;

use History\Entities\Models\Request;

class RequestsController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $requests = Request::with('votes')->get();
        $requests = $requests->sortByDesc(function (Request $request) {
            return $request->votes->count();
        });

        return $this->views->render('requests/index.twig', [
            'requests' => $requests,
        ]);
    }
}
