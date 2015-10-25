<?php
namespace History\Services\Internals;

use History\Services\Internals\Commands\Body;
use Illuminate\Contracts\Cache\Repository;
use Rvdv\Nntp\Client;
use SplFixedArray;

class Internals
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $group;
    /**
     * @var Repository
     */
    private $cache;

    /**
     * Internals constructor.
     *
     * @param Repository $cache
     * @param Client     $client
     * @param array      $group
     */
    public function __construct(Repository $cache, Client $client, array $group)
    {
        $this->cache  = $cache;
        $this->client = $client;
        $this->group  = $group;
    }

    /**
     * @return integer
     */
    public function getTotalNumberArticles()
    {
        return $this->group['count'];
    }

    /**
     * List all availables articles
     *
     * @param integer $from
     * @param integer $to
     *
     * @return SplFixedArray
     */
    public function getArticles($from, $to)
    {
        return $this->cache->rememberForever($from.'-'.$to, function () use ($from, $to) {
            $format  = $this->client->overviewFormat()->getResult();
            $command = $this->client->xover($from, $to, $format);

            return $command->getResult();
        });
    }

    /**
     * @param integer $article
     *
     * @return \Rvdv\Nntp\Command\CommandInterface
     */
    public function getArticleBody($article)
    {
        return $this->client
            ->sendCommand(new Body($article))
            ->getResult();
    }
}
