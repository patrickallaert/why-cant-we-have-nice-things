<?php

namespace History\CommandBus\Commands;

use History\CommandBus\CommandInterface;

class CreateCommentCommand implements CommandInterface
{
    /**
     * @var string
     */
    public $group;

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
}
