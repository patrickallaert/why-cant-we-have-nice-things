<?php

namespace History\Services\RequestsGatherer;

use History\CommandBus\Commands\CreateRequestCommand;
use History\CommandBus\Commands\CreateRequestHandler;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\TestCase;

class RequestsGathererTest extends TestCase
{
    /**
     * @var string
     */
    protected $url = 'http://rfc.com/constant';

    public function testCanCreateRequests()
    {
        $this->mockCache([
            RequestsGatherer::DOMAIN.'/rfc' => $this->getDummyPage('rfcs'),
            RequestsGatherer::DOMAIN.'/rfc/void_return_type' => '',
            RequestsGatherer::DOMAIN.'/rfc/revisit-trailing-comma-function-args' => '',
            RequestsGatherer::DOMAIN.'/rfc/closurefromcallable' => '',
        ]);

        $gatherer = $this->container->get(RequestsGatherer::class);
        $requests = $gatherer->createRequests();

        $this->assertCount(3, $requests);
    }

    public function testCanCreateRequest()
    {
        $this->mockCache([
            $this->url => $this->getDummyPage('rfc'),
        ]);

        $command = $this->container->get(CreateRequestHandler::class);
        $request = $command->handle(new CreateRequestCommand($this->url));

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testCancelsOnRfcsWithNoNames()
    {
        $this->mockCache([
            $this->url => '',
        ]);

        $command = $this->container->get(CreateRequestHandler::class);
        $request = $command->handle(new CreateRequestCommand($this->url));

        $this->assertNull($request);
    }

    public function testDoesntOverwriteUserWithEmptyInformations()
    {
        $this->mockCache([
            'http://people.php.net/foobar' => '',
            $this->url => $this->getDummyPage('rfc'),
        ]);

        $command = $this->container->get(CreateRequestHandler::class);
        $user = $command->createUser('foobar');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('foobar', $user->name);
    }

    public function testDoesntCrashOnInvalidCrawler()
    {
        $this->mockCache([
            'http://people.php.net/foobar' => false,
        ]);

        $command = $this->container->get(CreateRequestHandler::class);
        $command->createUser('foobar');
    }
}
