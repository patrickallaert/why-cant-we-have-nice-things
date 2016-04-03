<?php

namespace History\Services\Internals;

use PhpMimeMailParser\Parser;

class MailingListArticleCleaner
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * MailingListArticleCleaner constructor.
     *
     * @param Parser $parser
     */
    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    /**
     * @param string $message
     *
     * @return string
     * @throws \Exception
     */
    public function cleanup(string $message): string
    {
        // Do some preformatting
        $message = str_replace("=\n", " =\n", $message);

        // Get parsed message
        $contents = $this->parser->setText($message)->getMessageBody();

        // Cleanup contents, remove PGP signatures
        $contents = preg_replace('/ +/', ' ', $contents);
        $contents = $this->removePgpSignature($contents);
        $contents = preg_replace("/([a-zA-Z0-9])\r\n([a-zA-Z0-9])/", '$1<br>$2', $contents);
        $contents = trim($contents, " .\r\n-");

        return $contents;
    }

    /**
     * @param $contents
     *
     * @return mixed
     */
    private function removePgpSignature($contents)
    {
        $contents = str_replace('PGP signature:', null, $contents);
        $contents = preg_replace('/-----BEGIN PGP SIGNED MESSAGE-----\r\nHash: (.+)\r\n/', '', $contents);
        $contents = preg_replace('/(.*)([\-]+BEGIN PGP[-\s\S]+END PGP SIGNATURE[\-]+?)(.*)/', '$1$3', $contents);

        // If we still got PGP it means we have no end boundary
        // so just strip everything till the end
        if (strpos($contents, 'BEGIN PGP') !== false) {
            $contents = preg_replace('/(.*)([\-]+BEGIN PGP SIGNATURE[\-\s\S]+)/', '$1', $contents);
        }

        return $contents;
    }
}
