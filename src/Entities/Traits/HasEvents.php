<?php
namespace History\Entities\Traits;

use History\Entities\Models\Event;

trait HasEvents
{
    /**
     * @return mixed
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'eventable');
    }
}
