<?php
namespace History\Services\Internals\Commands;

use Rvdv\Nntp\Command\Command;
use Rvdv\Nntp\Response\ResponseInterface;

class Xpath extends Command
{
    /**
     * @var int
     */
    const FOUND_PATH = 223;

    /**
     * @var int
     */
    const INVALID_REFERENCE = 501;

    /**
     * @var string
     */
    private $reference;

    /**
     * Constructor.
     *
     * @param string $reference The reference
     */
    public function __construct($reference)
    {
        $this->reference = $reference;

        parent::__construct([], true);
    }

    /**
     * @return string
     */
    public function execute()
    {
        return sprintf('XPATH %s', $this->reference);
    }

    /**
     * @return array
     */
    public function getExpectedResponseCodes()
    {
        return [
            self::FOUND_PATH        => 'onFoundPath',
            self::INVALID_REFERENCE => 'onInvalidMessage',
        ];
    }

    /**
     * @param ResponseInterface $response
     */
    public function onFoundPath(ResponseInterface $response)
    {
        $this->result = str_replace('/', ':', $response->getMessage());
    }

    /**
     *
     */
    public function onInvalidMessage()
    {
        $this->result = null;
    }
}
