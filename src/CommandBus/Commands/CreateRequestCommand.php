<?php

namespace History\CommandBus\Commands;

use History\CommandBus\CommandInterface;

class CreateRequestCommand implements CommandInterface
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
}
