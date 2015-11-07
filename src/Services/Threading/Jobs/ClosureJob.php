<?php

namespace History\Services\Threading\Jobs;

use Closure;

class ClosureJob extends Job
{
    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param Closure $closure
     * @param array   $arguments
     */
    public function __construct(Closure $closure, array $arguments = [])
    {
        $this->closure   = $closure;
        $this->arguments = $arguments;
    }

    public function run()
    {
        $this->result = call_user_func_array($this->closure, $this->arguments);

        $this->done = true;
        $this->setGarbage();
    }
}
