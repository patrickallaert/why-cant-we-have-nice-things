<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Version;

class VersionSynchronizer extends AbstractSynchronizer
{
    /**
     * Synchronize an entity with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize()
    {
        $request = $this->get('request_id');
        $version = Version::firstOrNew([
            'version'    => $this->get('version'),
            'request_id' => $request,
        ]);

        $version->name       = $this->get('name');
        $version->request_id = $request;
        $this->updateTimestamps($version, $this->get('timestamp'));

        return $version;
    }
}
