<?php

namespace History\Services\Internals;

use Carbon\Carbon;
use History\CommandBus\Commands\CreateCommentCommand;
use History\Console\HistoryStyle;
use History\Entities\Models\Threads\Comment;
use History\Entities\Models\Threads\Group;
use History\Services\Traits\HasAsyncCapabilitiesTrait;
use League\Tactician\CommandBus;

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
            $this->synchronizeArticlesForGroup($group);
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
        $groups = $this->internals->getGroups();
        foreach ($this->output->progressIterator($groups) as $key => $group) {
            $attributes = array_except($group, ['status']);

            /** @var Group $group */
            $group = Group::firstOrCreate(['name' => $attributes['name']]);
            $group->fill($attributes);
            $group->saveIfDirty();

            $groups[$key] = $group;
        }

        return $this->group ? [$groups[$this->group]] : $groups;
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

        // Prepare main loop variables
        $queue = $this->getArticlesQueue($group);
        if (!$queue) {
            return $this->output->writeln('<info>No new articles</info>');
        }

        $this->output->writeln('Creating articles');
        $this->dispatchCommands($queue);
    }

    protected function synchronizeReferences()
    {
        $this->refreshParsedList();

        /** @var Comment[] $withReferences */
        $withReferences = Comment::whereNotNull('reference')->whereNull('comment_id')->get();
        $withReferences = $this->output->progressIterator($withReferences);
        foreach ($withReferences as $comment) {
            $reference = $this->getCommentFromReference($comment->references);
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
     * Get a list of articles to process
     * from this group
     *
     * @param Group $group
     *
     * @return CreateCommentCommand[]
     */
    protected function getArticlesQueue(Group $group): array
    {
        $to = (int) $group->high;
        $from = (int) $group->low;

        // Cutoff
        $date = null;
        $cutoff = 5;

        $queue = [];
        for ($i = $to; $i >= $from; $i--) {

            // Check if we've already parsed that
            // article, if yes skip it
            $xref = $group->name.':'.$i;
            foreach ($this->parsed as $references) {
                if (strpos($references, $xref) !== false) {
                    continue 2;
                }
            }

            // Check if the group has articles
            // during the last X years
            if (!$date) {
                $comment = $this->internals->getArticle($i);
                $date = Carbon::instance($comment['date']);
                if ($date->diffInYears() > $cutoff) {
                    $this->output->error('No articles in the last '.$cutoff.' years');
                    break;
                }
            }

            // If we have enough commands, stop here, else queue it
            $queue[] = new CreateCommentCommand($group->name, $i);
            if ($this->size && count($queue) >= $this->size) {
                return $queue;
            }
        }

        return $queue;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string[] $references
     *
     * @return int|null
     */
    protected function getCommentFromReference(array $references)
    {
        // Just get the last reference cause
        if (!$references) {
            return;
        }

        // Try to retrieve the comment the reference's about
        $reference = last($references);
        $reference = $this->internals->findArticleFromReference($reference);
        $comment = $reference && in_array($reference, $this->parsed, true)
            ? array_search($reference, $this->parsed, true)
            : null;

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
