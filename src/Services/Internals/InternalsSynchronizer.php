<?php

namespace History\Services\Internals;

use History\CommandBus\Commands\CreateCommentCommand;
use History\Console\HistoryStyle;
use History\Entities\Models\Comment;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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
     * InternalsSynchronizer constructor.
     *
     * @param CommandBus $bus
     * @param Internals  $internals
     */
    public function __construct(CommandBus $bus, Internals $internals)
    {
        $this->internals = $internals;
        $this->output    = new HistoryStyle(new ArrayInput([]), new NullOutput());
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
        $queue = $this->getArticlesQueue();

        $this->output->writeln('Creating comments');

        return $this->output->progressIterator($queue, function (CreateCommentCommand $command) use (&$created) {
            return $this->bus->handle($command);
        });
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
        $this->output->writeln('Getting messages');
        $this->output->progressStart($count - $this->size);
        for ($i = $from; $i <= $count; $i += self::CHUNK) {
            $to = $i + (self::CHUNK - 1);

            // Process this chunk of articles
            $articles = $this->internals->getArticles($i, $to);
            foreach ($articles as $article) {
                if (!$article) {
                    break;
                }

                $command             = new CreateCommentCommand();
                $command->xref       = $article['xref'];
                $command->subject    = $article['subject'];
                $command->references = $article['references'];
                $command->from       = $article['from'];
                $command->number     = $article['number'];
                $command->date       = $article['date'];

                $queue[] = $command;
            }

            $this->output->progressAdvance(self::CHUNK);
        }

        $this->output->progressFinish();

        return $queue;
    }
}
