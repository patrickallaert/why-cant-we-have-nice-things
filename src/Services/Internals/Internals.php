<?php

namespace History\Services\Internals;

use History\Services\Internals\Commands\Body;
use History\Services\Internals\Commands\Xpath;
use Illuminate\Contracts\Cache\Repository;
use Rvdv\Nntp\ClientInterface;
use Rvdv\Nntp\Command\XpathCommand;
use SplFixedArray;

class Internals
{
    /**
     * @var ClientInterface
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
     * @param Repository      $cache
     * @param ClientInterface $client
     */
    public function __construct(Repository $cache, ClientInterface $client)
    {
        $this->cache = $cache;
        $this->client = $client;
    }

    /**
     * @return int
     */
    public function getTotalNumberArticles()
    {
        $this->connectIfNeeded();

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
        return $this->cacheRequest('internals:xover:'.$from.'-'.$to, function () use ($from, $to) {
            $format = $this->client->overviewFormat()->getResult();
            $command = $this->client->xover($from, $to, $format);

            return (array) $command->getResult();
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
        $article = $this->cacheRequest('internals:body:'.$article, function () use ($article) {
            return $this->client
                ->sendCommand(new Body($article))
                ->getResult();
        });

        return $cleaner->cleanup($article);
    }

    /**
     * @param string $xpath
     *
     * @return string
     */
    public function findArticleFromReference($xpath)
    {
        $reference = $this->cacheRequest('internals:xpath:'.$xpath, function () use ($xpath) {
            return $this->client->sendCommand(new XpathCommand($xpath))->getResult();
        });

        return str_replace('/', ':', $reference);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Execute a request and cache it.
     *
     * @param string   $key
     * @param callable $callback
     *
     * @return mixed
     */
    protected function cacheRequest($key, callable $callback)
    {
        $tag = strpos($key, 'xover') !== false ? 'php' : 'internals';

        return $this->cache->tags($tag)->rememberForever($key, function () use ($callback) {
            $this->connectIfNeeded();

            return $callback();
        });
    }

    /**
     * Connect if needed.
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
