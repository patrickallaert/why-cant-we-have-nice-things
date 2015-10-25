<?php
namespace History\Services\Internals\Commands;

use Rvdv\Nntp\Command\Command;
use Rvdv\Nntp\Response\MultiLineResponseInterface;

class Body extends Command
{
    /**
     * @var integer
     */
    const BODY_RECEIVED = 222;

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
            self::BODY_RECEIVED => 'onBodyReceived',
        ];
    }

    /**
     * @param MultiLineResponseInterface $response
     */
    public function onBodyReceived(MultiLineResponseInterface $response)
    {
        $this->result = [];
        foreach ($response->getLines() as $line) {
            $this->result[] = $line;
        }
    }
}
