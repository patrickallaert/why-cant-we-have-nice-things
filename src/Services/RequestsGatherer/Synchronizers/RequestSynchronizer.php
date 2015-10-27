<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use DateTime;
use History\Entities\Models\Request;
use Illuminate\Support\Str;

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

        $request             = Request::firstOrNew(['link' => $this->get('link')]);
        $request->name       = $this->get('name');
        $request->contents   = $contents;
        $request->condition  = $this->get('condition');
        $request->status     = $this->get('status');

        // Prevent false positive of dirty attributes
        $timestamp = $this->get('timestamp') ?: new DateTime();
        if (!$request->created_at || $timestamp->format('Y-m-d') !== $request->created_at->format('Y-m-d')) {
            $request->created_at = $timestamp;
            $request->updated_at = $timestamp;
        }

        return $request;
    }
}
