<?php

namespace History\Entities\Traits;

use DateTime;
use History\Entities\Models\Event;

trait HasEventsTrait
{
    /**
     * Register an event.
     *
     * @param string $type
     * @param array  $metadata
     */
    public function registerEvent($type, array $metadata = [])
    {
        $attributes = [
            'type' => $type,
            'metadata' => $metadata,
        ];

        // Assign the entity's timestamps to the event
        if ($type !== 'rfc_status') {
            $attributes = array_merge($attributes, [
                'created_at' => $this->created_at ?: new DateTime(),
                'updated_at' => $this->updated_at ?: new DateTime(),
            ]);
        }

        $this->events()->create($attributes);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'eventable')->latest();
    }
}
