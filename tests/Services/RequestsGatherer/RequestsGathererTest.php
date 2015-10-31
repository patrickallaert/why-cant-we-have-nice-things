<?php
namespace History\Services\RequestsGatherer;

use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\TestCase;

class RequestsGathererTest extends TestCase
{
    protected $url = 'http://rfc.com/constant';

    public function testCanCreateRequests()
    {
        $cache = $this->mockCache([
            RequestsGatherer::DOMAIN.'/rfc'                                      => $this->getDummyPage('rfcs'),
            RequestsGatherer::DOMAIN.'/rfc/void_return_type'                     => '',
            RequestsGatherer::DOMAIN.'/rfc/revisit-trailing-comma-function-args' => '',
            RequestsGatherer::DOMAIN.'/rfc/closurefromcallable'                  => '',
        ]);

        $gatherer = new RequestsGatherer($cache);
        $requests = $gatherer->createRequests();

        $this->assertCount(3, $requests);
    }

    public function testCanCreateRequest()
    {
        $cache = $this->mockCache([
            $this->url => $this->getDummyPage('rfc'),
        ]);

        $gatherer = new RequestsGatherer($cache);
        $request  = $gatherer->createRequest($this->url);

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testCancelsOnRfcsWithNoNames()
    {
        $cache = $this->mockCache([
            $this->url => '',
        ]);

        $gatherer = new RequestsGatherer($cache);
        $request  = $gatherer->createRequest($this->url);

        $this->assertNull($request);
    }

    public function testDoesntOverwriteUserWithEmptyInformations()
    {
        $cache = $this->mockCache([
            'http://people.php.net/foobar' => '',
            $this->url                     => $this->getDummyPage('rfc'),
        ]);

        $gatherer = new RequestsGatherer($cache);
        $user     = $gatherer->createUser('foobar');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('foobar', $user->name);
    }

    public function testDoesntCrashOnInvalidCrawler()
    {
        $cache = $this->mockCache([
            'http://people.php.net/foobar' => false,
        ]);

        $gatherer = new RequestsGatherer($cache);
        $gatherer->createUser('foobar');
    }
}
