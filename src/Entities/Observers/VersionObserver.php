<?php

namespace History\Entities\Observers;

use History\Entities\Models\Version;

class VersionObserver
{
    /**
     * @param Version $version
     */
    public function created(Version $version)
    {
        $version->request->registerEvent('rfc_version', [
            'version' => $version->version,
        ]);
    }
}
