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
     * @var array
     */
    protected $group;

    /**
     * Internals constructor.
     *
     * @param Client $client
     * @param array  $group
     */
    public function __construct(Client $client, array $group)
    {
        $this->client = $client;
        $this->group  = $group;
    }

    /**
     * List all availables articles
     *
     * @param int $number
     *
     * @return array
     */
    public function getLatestArticles($number = 200)
    {
        $format = $this->client->overviewFormat()->getResult();
        $from   = $this->group['last'];
        $to     = $from - $number;

        $command = $this->client->xover($to, $from, $format);

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
