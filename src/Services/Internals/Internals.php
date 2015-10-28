<?php
namespace History\Services\Internals;

use History\Services\Internals\Commands\Body;
use Illuminate\Contracts\Cache\Repository;
use Rvdv\Nntp\ClientInterface;
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
     * @var resource
     */
    protected $socket;

    /**
     * Internals constructor.
     *
     * @param Repository      $cache
     * @param ClientInterface $client
     */
    public function __construct(Repository $cache, ClientInterface $client)
    {
        $this->cache  = $cache;
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
        return $this->cacheRequest($from.'-'.$to, function () use ($from, $to) {
            $format = $this->client->overviewFormat()->getResult();
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
     * @param string $xpath
     *
     * @return string
     */
    public function findArticleFromReference($xpath)
    {
        // Streamed sockets don't play well with XPATH for
        // some reason so using a simple socket here
        if (!$this->socket) {
            //$this->client->disconnect();
            $this->socket = fsockopen('tcp://news.php.net', 119);
        }

        return $this->cache->rememberForever('xpath.'.$xpath, function () use ($xpath) {
            fputs($this->socket, 'XPATH '.$xpath."\r\n");
            $response = fgets($this->socket, 1024);
            list($code, $reference) = explode(' ', $response, 2);
            $reference = str_replace('/', ':', $reference);
            $reference = trim($reference, "\r\n");

            return $code === '223' ? $reference : null;
        });
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
        return $this->cache->rememberForever($key, function () use ($callback) {
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
