<?php
namespace History\Services\RequestsGatherer;

use History\Entities\Models\Request;
use History\TestCase;

class RequestsGathererTest extends TestCase
{
    public function testCanCreateRequest()
    {
        $url   = 'http://rfc.com/constant';
        $cache = $this->mockCache([
            $url => $this->getDummyPage('rfc'),
        ]);

        $gatherer = new RequestsGatherer($cache);
        $request  = $gatherer->createRequest($url);

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testCancelsOnRfcsWithNoNames()
    {
        $url   = 'http://rfc.com/constant';
        $cache = $this->mockCache([
            $url => '',
        ]);

        $gatherer = new RequestsGatherer($cache);
        $request  = $gatherer->createRequest($url);

        $this->assertNull($request);

    }
}
