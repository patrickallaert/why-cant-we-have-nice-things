<?php
namespace History\Services\Internals;

use History\Services\Internals\Commands\Body;
use History\Services\Internals\Commands\Xpath;
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
     */
    public function __construct(Repository $cache, Client $client)
    {
        $this->cache  = $cache;
        $this->client = $client;
    }

    /**
     * @return int
     */
    public function getTotalNumberArticles()
    {
        return $this->group ? $this->group['count'] : 90000;
    }

    /**
     * List all availables articles.
     *
     * @param int $from
     * @param int $to
     *
     * @return SplFixedArray
     */
    public function getArticles($from, $to)
    {
        return $this->cacheRequest($from.'-'.$to, function () use ($from, $to) {
            $format  = $this->client->overviewFormat()->getResult();
            $command = $this->client->xover($from, $to, $format);

            return $command->getResult();
        });
    }

    /**
     * @param int $article
     *
     * @return string
     */
    public function getArticleBody($article)
    {
        $cleaner = new MailingListArticleCleaner();
        $article = $this->cacheRequest('body-'.$article, function () use ($article) {
            return $this->client
                ->sendCommand(new Body($article))
                ->getResult();
        });

        return $cleaner->cleanup($article);
    }

    /**
     * @param string $reference
     *
     * @return string
     */
    public function findArticleFromReference($reference)
    {
        return $this->cache->rememberForever('xpath-'.$reference, function () use ($reference) {
            return $this->client
                ->sendCommand(new Xpath($reference))
                ->getResult();
        });
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Execute a request and cache it
     *
     * @param string   $key
     * @param callable $callback
     *
     * @return mixed
     */
    protected function cacheRequest($key, callable $callback)
    {
        return $this->cache->rememberForever($key, function () use ($callback) {
            $this->connectIfNeeded();

            return $callback();
        });
    }

    /**
     * Connect if needed
     */
    protected function connectIfNeeded()
    {
        if ($this->group) {
            return;
        }

        // Get php.internals group
        $this->client->connect();
        $this->group = $this->client->group('php.internals')->getResult();
    }
}
