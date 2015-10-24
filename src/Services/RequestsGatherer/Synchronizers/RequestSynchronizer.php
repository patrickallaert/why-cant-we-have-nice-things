<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use DateTime;
use History\Entities\Models\Request;

class RequestSynchronizer extends AbstractSynchronizer
{
    /**
     * Synchronize an user with our domain.
     *
     * @return Request
     */
    public function synchronize()
    {
        $timestamp = $this->get('timestamp') ?: new DateTime();

        $request             = Request::firstOrNew(['link' => $this->get('link')]);
        $request->name       = $this->get('name');
        $request->condition  = $this->get('condition');
        $request->status     = $this->get('status');
        $request->created_at = $timestamp;
        $request->updated_at = $timestamp;

        return $request;
    }
}
