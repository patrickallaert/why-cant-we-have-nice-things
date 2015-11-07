<?php

namespace History\CommandBus\Commands;

use History\CommandBus\ThreadedCommandInterface;

class CreateRequestCommand implements ThreadedCommandInterface
{
    /**
     * @var string
     */
    public $request;

    /**
     * CreateRequestCommand constructor.
     *
     * @param string $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->request;
    }
}
