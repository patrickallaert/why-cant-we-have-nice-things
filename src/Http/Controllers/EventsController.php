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
        $parameters = new Fluent($request->getQueryParams());
        $types = (array) $parameters->get('types');

        // Get the RFCs and events
        $events = $this->getEvents($request, $types);
        $voting = Request::where('status', Request::VOTING)->get();

        return $this->render('events/index.twig', [
            'filter' => $types,
            'voting' => $voting,
            'events' => $events,
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array                  $types
     *
     * @return array
     */
    protected function getEvents(ServerRequestInterface $request, array $types)
    {
        // Get the events we're interested in
        $events = Event::with('eventable.user', 'eventable.request')
            ->where('type', '!=', Event::TYPES[0])
            ->latest();
        if ($types) {
            $events = $events->whereIn('type', $types);
        }

        $events = $this->paginate($events, $request);

        return $events;
    }
}
