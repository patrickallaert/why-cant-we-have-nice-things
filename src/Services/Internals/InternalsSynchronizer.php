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
     * @return Comment[][]
     */
    public function synchronize()
    {
        $this->refreshParsedList();

        $this->output->writeln('Getting groups');
        $groups = $this->synchronizeGroups();

        $created = [];
        foreach ($groups as $group) {
            $created[$group->name] = $this->synchronizeArticlesForGroup($group);
        }

        $this->output->writeln('Binding references');
        $this->synchronizeReferences();

        return $created;
    }

    /**
     * Synchronize the groups with the ones
     * on the server.
     *
     * @return Group[]
     */
    protected function synchronizeGroups(): array
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
     * Synchronize all articles found in a group.
     *
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

        if (!$queue) {
            return [];
        }

        $this->output->writeln('Creating comments');
        $comments = $this->dispatchCommands($queue);

        return $comments;
    }

    protected function synchronizeReferences()
    {
        $this->refreshParsedList();

        /** @var Comment[] $withReferences */
        $withReferences = Comment::whereNotNull('reference')->whereNull('comment_id')->get();
        $withReferences = $this->output->progressIterator($withReferences);
        foreach ($withReferences as $comment) {
            $reference = $this->getCommentFromReference($comment->reference);
            if (!$reference) {
                continue;
            }

            // Mark reference as parent
            $comment->comment_id = $reference;
            $comment->save();
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// COMMANDS //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a queue of CreateComment commands
     * we need to handle for this group.
     *
     * @param Group $group
     *
     * @return CreateCommentCommand[]
     */
    protected function getArticlesQueue(Group $group): array
    {
        $groupHigh = $group->high ?: self::CHUNK;

        // Start at the last comment we parsed
        $from = $group->low;
        $chunk = min(self::CHUNK, $groupHigh);
        $max = $this->size ? min($groupHigh, $this->size) : $groupHigh;

        $queue = [];
        $this->output->progressStart($max);
        for ($i = $from; $i <= $max; $i += $chunk) {
            $to = $i + ($chunk - 1);

            // Process this chunk of articles
            try {
                $articles = $this->internals->getArticles($i, $to);

                foreach ($articles as $article) {
                    if ($command = $this->createCommandFromArticle($article, $group->name)) {
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
    protected function createCommandFromArticle($article, string $group)
    {
        if (!$article || !isset($article['from'])) {
            return;
        }

        // If we already synchronized this one, skip it
        if (in_array($article['xref'], $this->parsed, true)) {
            return;
        }

        $command = new CreateCommentCommand();
        $command->group = $group;
        $command->xref = $article['xref'];
        $command->subject = $article['subject'];
        $command->reference = $article['references'];
        $command->from = $article['from'];
        $command->number = $article['number'];
        $command->date = $article['date'];

        return $command;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $references
     *
     * @return int|null
     */
    protected function getCommentFromReference(string $references)
    {
        // Just get the last reference cause
        $references = array_filter(explode('>', $references));
        $reference = last($references);
        $reference = $reference ? trim($reference).'>' : null;
        if (!$reference) {
            return;
        }

        // Try to retrieve the comment the reference's about
        $reference = $this->internals->findArticleFromReference($reference);
        $comment = $reference && in_array($reference, $this->parsed, true) ? array_search($reference, $this->parsed, true) : null;

        return $comment;
    }

    /**
     * Refresh the list of parsed comments.
     */
    protected function refreshParsedList()
    {
        $this->parsed = Comment::lists('xref')->all();
    }
}
