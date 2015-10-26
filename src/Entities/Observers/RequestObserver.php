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
        $request->registerEvent('rfc_created');
    }

    /**
     * @param Request $request
     */
    public function updating(Request $request)
    {
        if ($request->isDirty('status')) {
            $request->registerEvent('rfc_status', [
                'new_status' => $request->status,
            ]);
        }
    }
}
