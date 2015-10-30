<?php
namespace History\Http\Controllers;

use History\Entities\Models\Event;
use History\Entities\Models\Request;
use Illuminate\Support\Fluent;
use Psr\Http\Message\ServerRequestInterface;

class EventsController extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function index(ServerRequestInterface $request)
    {
        // Get which types of events to show
        $parameters = new Fluent($request->getQueryParams());
        $types      = (array) $parameters->get('types');

        // Get the events we're interested in
        $events = Event::with('eventable.question.request', 'eventable.user')->latest();
        if ($types) {
            $events = $events->whereIn('type', $types);
        }

        // Get the RFCs currently in voting
        $voting = Request::where('status', Request::VOTING)->get();

        return $this->views->render('events/index.twig', [
            'filter' => $types,
            'voting' => $voting,
            'events' => $this->paginate($events, $request),
        ]);
    }
}
