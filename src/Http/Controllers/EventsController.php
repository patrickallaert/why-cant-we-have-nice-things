<?php
namespace History\Http\Controllers;

use History\Entities\Models\Event;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class EventsController extends AbstractController
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function index(ServerRequestInterface $request)
    {
        $parameters = new ParameterBag($request->getQueryParams());
        $events     = Event::with('eventable.question.request', 'eventable.user')
                       ->latest()
                       ->paginate(
                           50, ['*'], 'page',
                           $parameters->get('page')
                       );

        $events->setPath($request->getUri()->getPath());

        return $this->views->render('events/index.twig', [
            'events' => $events,
        ]);
    }
}
