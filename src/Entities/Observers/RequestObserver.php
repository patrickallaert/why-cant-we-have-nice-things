<?php
namespace History\Entities\Observers;

use History\Entities\Models\Request;

class RequestObserver
{
    /**
     * @param Request $request
     */
    public function created(Request $request)
    {
        $request->events()->create([
            'type'       => 'rfc_created',
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
        ]);
    }
}
