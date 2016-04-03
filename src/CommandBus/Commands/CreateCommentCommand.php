<?php

namespace History\CommandBus\Commands;

use History\CommandBus\CommandInterface;

class CreateCommentCommand implements CommandInterface
{
    /**
     * @var int
     */
    public $articleNumber;

    /**
     * @var string
     */
    public $group;

    /**
     * @param string $group
     * @param int    $articleNumber
     */
    public function __construct(string $group, int $articleNumber)
    {
        $this->articleNumber = $articleNumber;
        $this->group = $group;
    }
}
