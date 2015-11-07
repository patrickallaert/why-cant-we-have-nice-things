<?php

namespace History\Services\Threading;

use Worker;

class Autoloader extends Worker
{
    /**
     * @var string
     */
    protected $loader;

    /**
     * @param string $loader
     */
    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        require_once $this->loader;
    }

    /**
     * {@inheritdoc}
     */
    public function start($options = null)
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}
