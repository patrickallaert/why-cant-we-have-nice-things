<?php
namespace History\Services\RequestsGatherer\Synchronizers;

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
        $contents = $this->get('contents');
        $contents = utf8_encode($contents);

        $request            = Request::firstOrNew(['link' => $this->get('link')]);
        $request->name      = $this->get('name');
        $request->contents  = $contents;
        $request->condition = $this->get('condition');
        $request->status    = $this->get('status');

        // Prevent false positive of dirty attributes
        $this->updateTimestamps($request, $this->get('timestamp'));

        return $request;
    }
}
