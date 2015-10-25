<?php
namespace History\Services\Internals\Commands;

use Rvdv\Nntp\Command\Command;
use Rvdv\Nntp\Response\MultiLineResponseInterface;
use Rvdv\Nntp\Response\ResponseInterface;

class Body extends Command
{
    /**
     * @var integer
     */
    const BODY_RECEIVED = 222;

    /**
     * @var integer
     */
    const NO_SUCH_ARTICLE = 423;

    /**
     * @var string
     */
    private $article;

    /**
     * Constructor
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
        return sprintf('BODY %s', $this->article);
    }

    /**
     * @return array
     */
    public function getExpectedResponseCodes()
    {
        return [
            self::BODY_RECEIVED   => 'onBodyReceived',
            self::NO_SUCH_ARTICLE => 'onArticleNotFound',
        ];
    }

    /**
     * @param MultiLineResponseInterface $response
     */
    public function onBodyReceived(MultiLineResponseInterface $response)
    {
        // Convert to array and remove encoding lines
        $body = (array) $response->getLines();
        $body = implode(PHP_EOL, $body);

        $this->result = trim($body);
    }

    /**
     * @param ResponseInterface $response
     */
    public function onArticleNotFound(ResponseInterface $response)
    {
        $this->result = $response->getMessage();
    }
}
