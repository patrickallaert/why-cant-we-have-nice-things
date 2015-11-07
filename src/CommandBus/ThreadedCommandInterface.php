<?php

namespace History\CommandBus;

interface ThreadedCommandInterface extends CommandInterface
{
    /**
     * @return string
     */
    public function getIdentifier();
}
