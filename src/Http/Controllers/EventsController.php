<?php
namespace History\Http\Controllers;

use History\Entities\Models\Event;
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

        return $this->views->render('events/index.twig', [
            'events' => $this->paginate($events, $request),
        ]);
    }
}
