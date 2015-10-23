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
            return $request->created_at;
        });

        // Compute % of passed RFCs
        $voted = $requests->filter(function (Request $request) {
            return $request->votes->count();
        });

        $passed = $voted->filter(function (Request $request) {
            return $request->passed;
        });

        return $this->views->render('requests/index.twig', [
            'requests' => $requests,
            'voted'    => $voted,
            'passed'   => $passed->count() / $voted->count(),
        ]);
    }

    /**
     * @param int $request
     *
     * @return string
     */
    public function show($request)
    {
        $request = Request::with('votes.user')->findOrFail($request);

        return $this->views->render('requests/show.twig', [
            'request' => $request,
        ]);
    }
}
