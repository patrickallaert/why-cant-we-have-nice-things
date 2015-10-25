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

    /**
     * Register an event
     *
     * @param string $type
     * @param array  $metadata
     */
    public function registerEvent($type, array $metadata = [])
    {
        $attributes = [
            'type'     => $type,
            'metadata' => $metadata,
        ];

        if ($type !== 'rfc_status') {
            $attributes = array_merge($attributes, [
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]);
        }

        $this->events()->create($attributes);
    }
}
