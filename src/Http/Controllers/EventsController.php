<?php
namespace History\Http\Controllers;

use History\Entities\Models\Event;
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
        $events = Event::with('eventable.question.request', 'eventable.user')->latest();
        $events = $this->paginate($events, $request);

        return $this->views->render('events/index.twig', [
            'events' => $events,
        ]);
    }
}
