<?php
namespace History\Services\Internals;

use History\Services\Internals\Commands\Body;
use Rvdv\Nntp\Client;

class Internals
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Internals constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * List all availables articles
     */
    public function listArticles()
    {
        $group = $this->client->group('php.internals')->getResult();

        $format = $this->client->overviewFormat()->getResult();
        $from   = $group['first'];
        $to     = $from + 100;

        $command = $this->client->xover($from, $to, $format);

        return $command->getResult();
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
