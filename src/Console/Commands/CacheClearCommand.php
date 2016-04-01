<?php

namespace History\Console\Commands;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends AbstractCommand
{
    /**
     * @var Repository
     */
    protected $repository;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param Repository         $repository
     */
    public function __construct(ContainerInterface $container, Repository $repository)
    {
        $this->repository = $repository;
        $this->container = $container;
    }

    /**
     * Run the command.
     *
     * @param bool            $all
     * @param OutputInterface $output
     */
    public function run($all, OutputInterface $output)
    {
        $this->wrapOutput($output);

        // Empty standard cache
        if ($all) {
            $this->repository->flush();
        } else {
            $this->repository->tags('php')->flush();
        }

        // Empty filesystem cache
        $files = new Filesystem();
        $cache = $this->container->get('paths.cache');
        foreach ($files->directories($cache) as $directory) {
            $files->deleteDirectory($directory);
        }

        $this->output->success('Cache emptied');
    }
}
