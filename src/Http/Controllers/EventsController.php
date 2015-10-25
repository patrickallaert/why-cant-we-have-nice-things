<?php
namespace History\Http\Controllers;

use History\Entities\Models\Event;

class EventsController extends AbstractController
{
    /**
     * @return string
     */
    public function index()
    {
        $events = Event::with('eventable.question.request', 'eventable.user')
                       ->latest()
                       ->paginate(
                           50, ['*'], 'page',
                           $this->request->get('page')
                       );

        $events->setPath('events');

        return $this->views->render('events/index.twig', [
            'events' => $events,
        ]);
    }
}
