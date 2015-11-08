<?php

namespace History\Services\Internals;

use History\CommandBus\Commands\CreateCommentCommand;
use History\Console\HistoryStyle;
use History\Entities\Models\Comment;
use League\Tactician\CommandBus;

class InternalsSynchronizer
{
    /**
     * @var int
     */
    const FIRST_RFC = 40000;

    /**
     * @var int
     */
    const CHUNK = 500;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var CommandBus
     */
    private $bus;

    /**
     * @var Internals
     */
    protected $internals;

    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @var array
     */
    protected $parsed;

    /**
     * InternalsSynchronizer constructor.
     *
     * @param CommandBus $bus
     * @param Internals  $internals
     */
    public function __construct(CommandBus $bus, Internals $internals)
    {
        $this->internals = $internals;
        $this->output    = new HistoryStyle();
        $this->bus       = $bus;
    }

    /**
     * @param HistoryStyle $output
     */
    public function setOutput(HistoryStyle $output)
    {
        $this->output = $output;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Synchronize the php.internals mailing list
     * to a fucking usable format.
     */
    public function synchronize()
    {
        $this->parsed = Comment::lists('xref')->all();

        $this->output->writeln('Getting messages');
        $queue = $this->getArticlesQueue();

        $created = [];
        $this->output->writeln('Creating comments');
        foreach ($this->output->progressIterator($queue, [$this->bus, 'handle']) as $comment) {
            $created[] = $comment;
        }

        return $created;
    }

    /**
     * @return array
     */
    protected function getArticlesQueue()
    {
        // Start at the last comment we parsed
        $count = $this->internals->getTotalNumberArticles();
        $from  = $this->size ? $count - $this->size : self::FIRST_RFC;

        $queue = [];
        $this->output->progressStart($count - $this->size);
        for ($i = $from; $i <= $count; $i += self::CHUNK) {
            $to = $i + (self::CHUNK - 1);

            // Process this chunk of articles
            $articles = $this->internals->getArticles($i, $to);
            foreach ($articles as $article) {
                if ($command = $this->processArticle($article)) {
                    $queue[] = $command;
                }
            }

            $this->output->progressAdvance(self::CHUNK);
        }

        $this->output->progressFinish();

        return $queue;
    }

    /**
     * @param array|null $article
     *
     * @return CreateCommentCommand
     */
    protected function processArticle($article)
    {
        if (!$article) {
            return;
        }

        // If we already synchronized this one, skip it
        if (array_key_exists($article['xref'], $this->parsed)) {
            return;
        }

        // If the article is not about RFCs, fuck off
        if (!preg_match('/(RFC|VOTE)/i', $article['subject'])) {
            return;
        }

        $command             = new CreateCommentCommand();
        $command->xref       = $article['xref'];
        $command->subject    = $article['subject'];
        $command->references = $article['references'];
        $command->from       = $article['from'];
        $command->number     = $article['number'];
        $command->date       = $article['date'];

        return $command;
    }
}
