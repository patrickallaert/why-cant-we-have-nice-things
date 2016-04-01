<?php

namespace History\Services\Threading;

use History\Application;
use Interop\Container\ContainerInterface;
use Worker;

/**
 * A Worker which loads Composer in context
 * before running its jobs, and boots the
 * application so that the DB is available.
 */
class AutoloadingWorker extends Worker
{
    /**
     * This has to be static to
     * be treated as thead-local.
     *
     * @var ContainerInterface
     */
    public static $container;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // Require dependencies
        require_once __DIR__.'/../../../vendor/autoload.php';

        // Boot the application and database and
        // everything we might need
        if (!static::$container) {
            static::$container = (new Application())->getContainer();
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return static::$container;
    }

    /**
     * {@inheritdoc}
     */
    public function start(int $options = null)
    {
        return parent::start(PTHREADS_INHERIT_NONE);
    }
}
