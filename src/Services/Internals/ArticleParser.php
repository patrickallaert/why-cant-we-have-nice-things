<?php

namespace History\Services\Internals;

use Carbon\Carbon;
use DateTime;
use Exception;
use PhpMimeMailParser\Parser;

class ArticleParser
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * ArticleParser constructor.
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
     * @throws Exception
     *
     * @return array
     */
    public function parse(string $message): array
    {
        // Do some preformatting
        $message = str_replace("=\n", " =\n", $message);
        $this->parser->setText($message);

        // Get parsed message
        return [
            'xref' => $this->getXref(),
            'message_id' => $this->parser->getHeader('message-id'),
            'group' => $this->parser->getHeader('path'),
            'subject' => $this->getSubject(),
            'references' => $this->getReferences(),
            'from' => $this->parser->getHeader('from'),
            'date' => $this->getDateTime(),
            'contents' => $this->getContents(),
        ];
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// SECTIONS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    protected function getXref(): string
    {
        return str_replace('news.php.net ', '', $this->parser->getHeader('xref'));
    }

    /**
     * @return string
     */
    protected function getSubject(): string
    {
        $subject = $this->parser->getHeader('subject');
        $subject = trim(strtr($subject, [
            'RE' => null,
            'Re:' => null,
            'RFC' => null,
            '[RFC]' => null,
            '[DISCUSSION]' => null,
            '[PHP-DEV]' => null,
            '[VOTE]' => null,
        ]), ' :');

        return ucfirst(strtolower($subject));
    }

    /**
     * @return DateTime
     */
    protected function getDateTime(): DateTime
    {
        $timezones = [
            'Eastern Daylight Time' => 'EDT',
            'Eastern Standard Time' => 'EST',
            'MET DST' => 'MET',
        ];

        // Try to change timezone to one PHP understands
        $date = strtr($this->parser->getHeader('date'), $timezones);
        $date = preg_replace('/(.+)\(.+\)$/', '$1', $date);

        try {
            $datetime = new Carbon($date);
        } catch (Exception $exception) {
            $datetime = Carbon::createFromDate(1970, 01, 01);
        }

        return $datetime;
    }

    /**
     * @return string[]
     */
    protected function getReferences(): array
    {
        $references = $this->parser->getHeader('references');
        $references = preg_split('/(?<=>)/', $references);
        $references = array_filter(array_map('trim', $references));

        return $references;
    }

    /**
     * @throws Exception
     *
     * @return false|mixed|string
     */
    protected function getContents()
    {
        $contents = $this->parser->getMessageBody();

        // Cleanup contents, remove PGP signatures
        $contents = preg_replace('/ +/', ' ', $contents);
        $contents = $this->removePgpSignature($contents);
        $contents = preg_replace("/([a-zA-Z0-9])\r\n([a-zA-Z0-9])/", '$1<br>$2', $contents);
        $contents = trim($contents, " .\r\n-");

        return $contents;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

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
