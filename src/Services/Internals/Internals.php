<?php

namespace History\Services\Internals;

use History\Services\Internals\Commands\Body;
use History\Services\Internals\Commands\Xpath;
use Illuminate\Contracts\Cache\Repository;
use Rvdv\Nntp\ClientInterface;
use Rvdv\Nntp\Command\ArticleCommand;
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

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// GROUPS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return \Rvdv\Nntp\Command\GroupCommand
     */
    public function getGroups()
    {
        $this->client->connect();

        return $this->client->listGroups()->getResult();
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group)
    {
        $this->connectIfNeeded();
        $this->group = $this->client->group($group)->getResult();
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// ARTICLES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

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
        return $this->cacheRequest('xover:'.$from.'-'.$to, function () use ($from, $to) {
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
    public function getArticleBody(int $article): string
    {
        $cleaner = new MailingListArticleCleaner();
        $article = $this->cacheRequest('body:'.$article, function () use ($article) {
            return $this->client
                ->sendCommand(new ArticleCommand($article))
                ->getResult();
        });

        if (!is_array($article)) {
            $article = explode("\r\n", $article);
        }

        return $cleaner->cleanup($article);
    }

    /**
     * @param string $xpath
     *
     * @return string
     */
    public function findArticleFromReference($xpath)
    {
        $reference = $this->cacheRequest($xpath, function () use ($xpath) {
            return $this->client->sendCommand(new XpathCommand($xpath))->getResult();
        });

        return str_replace('/', ':', $reference);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $hash
     *
     * @return string
     */
    protected function getHash(string $hash): string
    {
        if (!$this->group) {
            return 'internals:'.$hash;
        }

        return sprintf('internals:%s:%s', $this->group['name'], $hash);
    }

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
        $key = $this->getHash($key);

        return $this->cache->tags('internals')->rememberForever($key, function () use ($callback) {
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
    }
}
