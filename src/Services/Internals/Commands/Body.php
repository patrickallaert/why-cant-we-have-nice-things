<?php
namespace History\Services\Internals\Commands;

use Rvdv\Nntp\Command\Command;
use Rvdv\Nntp\Response\MultiLineResponseInterface;
use Rvdv\Nntp\Response\ResponseInterface;

class Body extends Command
{
    /**
     * @var int
     */
    const ARTICLE_RECEIVED = 220;

    /**
     * @var int
     */
    const NO_SUCH_ARTICLE = 423;

    /**
     * @var string
     */
    private $article;

    /**
     * Constructor.
     *
     * @param string $article The name of the group.
     */
    public function __construct($article)
    {
        $this->article = $article;

        parent::__construct([], true);
    }

    /**
     * @return string
     */
    public function execute()
    {
        return sprintf('ARTICLE %s', $this->article);
    }

    /**
     * @return array
     */
    public function getExpectedResponseCodes()
    {
        return [
            self::ARTICLE_RECEIVED => 'onArticleReceived',
            self::NO_SUCH_ARTICLE  => 'onArticleNotFound',
        ];
    }

    /**
     * @param MultiLineResponseInterface $response
     */
    public function onArticleReceived(MultiLineResponseInterface $response)
    {
        $this->result = (array) $response->getLines();
    }

    /**
     * @param ResponseInterface $response
     */
    public function onArticleNotFound(ResponseInterface $response)
    {
        $this->result = $response->getMessage();
    }
}
