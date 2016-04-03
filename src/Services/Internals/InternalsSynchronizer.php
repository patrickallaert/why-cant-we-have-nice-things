<?php

namespace History\Services\Internals;

use History\CommandBus\Commands\CreateCommentCommand;
use History\Console\HistoryStyle;
use History\Entities\Models\Threads\Comment;
use History\Entities\Models\Threads\Group;
use History\Services\Threading\HasAsyncCapabilitiesTrait;
use League\Tactician\CommandBus;
use Rvdv\Nntp\Exception\RuntimeException;

class InternalsSynchronizer
{
    use HasAsyncCapabilitiesTrait;

    /**
     * @var int
     */
    const CHUNK = 500;

    /**
     * @var int
     */
    protected $size;

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
     * @var string
     */
    protected $group;

    /**
     * InternalsSynchronizer constructor.
     *
     * @param CommandBus $bus
     * @param Internals  $internals
     */
    public function __construct(CommandBus $bus, Internals $internals)
    {
        $this->internals = $internals;
        $this->output = new HistoryStyle();
        $this->bus = $bus;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// OPTIONS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

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
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////// SYNCHRONIZATION //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Synchronize the mailing list
     * to a fucking usable format.
     *
     * @return Comment[]
     */
    public function synchronize()
    {
        $this->parsed = Comment::lists('xref')->all();

        $this->output->writeln('Getting groups');
        $groups = $this->synchronizeGroups();

        $created = [];
        foreach ($groups as $group) {
            $created[$group->name] = $this->synchronizeArticlesForGroup($group);
        }

        return $created;
    }

    /**
     * @return Group[]
     */
    protected function synchronizeGroups()
    {
        $groups = !$this->group ? $this->internals->getGroups() : [['name' => $this->group]];
        foreach ($this->output->progressIterator($groups) as $key => $group) {
            $attributes = array_except($group, ['status']);

            /** @var Group $group */
            $group = Group::firstOrCreate(['name' => $attributes['name']]);
            $group->fill($attributes);
            $group->saveIfDirty();

            $groups[$key] = $group;
        }

        return $groups;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    protected function synchronizeArticlesForGroup(Group $group)
    {
        $this->output->section($group->name);
        $this->internals->setGroup($group->name);

        $this->output->writeln('Getting messages informations');
        $queue = $this->getArticlesQueue($group);

        $this->output->writeln('Creating comments');
        $comments = $this->dispatchCommands($queue);

        return $comments;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    protected function getArticlesQueue(Group $group)
    {
        // Start at the last comment we parsed
        $from = $group->low;
        $chunk = min(self::CHUNK, $group->high);

        $queue = [];
        $this->output->progressStart($group->high);
        for ($i = $from; $i <= $group->high; $i += $chunk) {
            $to = $i + ($chunk - 1);

            // Process this chunk of articles
            try {
                $articles = $this->internals->getArticles($i, $to);

                foreach ($articles as $article) {
                    if ($command = $this->processArticle($article, $group->name)) {
                        $queue[] = $command;
                    }
                }
            } catch (RuntimeException $exception) {
                // No articles in this range
            }

            $this->output->progressAdvance($chunk);
        }

        $this->output->progressFinish();

        return $queue;
    }

    /**
     * @param array|null $article
     * @param string     $group
     *
     * @return CreateCommentCommand
     */
    protected function processArticle($article, string $group)
    {
        if (!$article || !isset($article['from'])) {
            return;
        }

        // If we already synchronized this one, skip it
        if (array_key_exists($article['xref'], $this->parsed)) {
            return;
        }

        $command = new CreateCommentCommand();
        $command->group = $group;
        $command->xref = $article['xref'];
        $command->subject = $article['subject'];
        $command->references = $article['references'];
        $command->from = $article['from'];
        $command->number = $article['number'];
        $command->date = $article['date'];

        return $command;
    }
}
