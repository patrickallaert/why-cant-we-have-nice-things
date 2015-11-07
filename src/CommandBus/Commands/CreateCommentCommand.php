<?php

namespace History\CommandBus\Commands;

use History\CommandBus\ThreadedCommandInterface;

class CreateCommentCommand implements ThreadedCommandInterface
{
    /**
     * @var string
     */
    public $xref;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $references;

    /**
     * @var string
     */
    public $from;

    /**
     * @var string
     */
    public $number;

    /**
     * @var string
     */
    public $date;

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->xref;
    }
}
