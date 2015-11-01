<?php
namespace History\Console\Commands;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Filesystem\Filesystem;
use Interop\Container\ContainerInterface;

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
        $this->container  = $container;
    }

    /**
     * Run the command.
     */
    public function run()
    {
        // Empty standard cache
        //$this->repository->flush();

        // Empty filesystem cache
        $files = new Filesystem();
        $cache = $this->container->get('paths.cache');
        foreach ($files->directories($cache) as $directory) {
            $files->deleteDirectory($directory);
        }

        $this->output->success('Cache emptied');
    }
}
