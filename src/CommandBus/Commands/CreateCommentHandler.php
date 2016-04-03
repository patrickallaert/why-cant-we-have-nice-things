<?php

namespace History\CommandBus\Commands;

use Carbon\Carbon;
use DateTime;
use Exception;
use History\Entities\Models\Comment;
use History\Entities\Models\Request;
use History\Entities\Models\Thread;
use History\Entities\Synchronizers\CommentSynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Services\IdentityExtractor;
use History\Services\Internals\Internals;
use Rvdv\Nntp\Exception\InvalidArgumentException;

class CreateCommentHandler extends AbstractHandler
{
    /**
     * @var CreateCommentCommand
     */
    protected $command;

    /**
     * @var Internals
     */
    protected $internals;

    /**
     * @var array
     */
    protected $parsed;

    /**
     * @var Thread
     */
    protected $thread;

    /**
     * CreateCommentHandler constructor.
     *
     * @param Internals $internals
     */
    public function __construct(Internals $internals)
    {
        $this->internals = $internals;
        $this->parsed = Comment::lists('id', 'xref')->all();
    }

    /**
     * @param \History\CommandBus\Commands\CreateCommentCommand $command
     *
     * @return Comment|void
     */
    public function handle(CreateCommentCommand $command)
    {
        $this->command = $command;

        // Get the RFC the message relates to
        $this->command->subject = $this->cleanupSubject($this->command->subject);
        if (!$request = $this->getRelatedRequest()) {
            return;
        }

        // Get the user that posted the message
        $user = $this->getRelatedUser();

        // If the article has references, find them
        $thread = $this->getParentThread($request, $user);
        $comment = $this->getCommentFromReference($this->command->references);

        // Grab the message contents and insert into database
        return $this->createCommentFromArticle($thread->id, $user, $comment);
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////// INFORMATIONS EXTRACTORS //////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Remove tags from an article's subject.
     *
     * @param string $subject
     *
     * @return string
     */
    protected function cleanupSubject($subject)
    {
        return trim(strtr($subject, [
            'RE' => null,
            'Re:' => null,
            'RFC' => null,
            '[RFC]' => null,
            '[DISCUSSION]' => null,
            '[PHP-DEV]' => null,
            '[VOTE]' => null,
        ]), ' :');
    }

    /**
     * @return DateTime
     */
    protected function getDatetime(): DateTime
    {
        // Normalize date
        try {
            $datetime = str_replace('(GMT Daylight Time)', '', $this->command->date);
            $datetime = new DateTime($datetime);
        } catch (Exception $exception) {
            $datetime = Carbon::now();
        }

        return $datetime;
    }

    /**
     * @param int $request
     * @param int $user
     *
     * @return Thread
     */
    protected function getParentThread(int $request, int $user): Thread
    {
        $thread = Thread::firstOrNew(['name' => $this->command->subject]);
        $datetime = $this->getDatetime();

        // Add additional attributes if we just
        // created the thread
        if (!$thread->exists) {
            $thread->request_id = $request;
            $thread->user_id = $user;
            $thread->created_at = $datetime;
            $thread->updated_at = $datetime;
            $thread->save();
        }

        return $thread;
    }

    /**
     * @return int|null
     */
    protected function getRelatedRequest()
    {
        $subject = $this->command->subject;
        $existingRequests = Request::lists('id', 'name');

        // Try to find an exact match
        if (array_key_exists($subject, $existingRequests)) {
            return $existingRequests[$subject];
        }

        // If the similarity between a message's title and an RFC name
        // is above 90%, consider a match
        foreach ($existingRequests as $name => $id) {
            similar_text(strtolower($subject), strtolower($name), $similarity);
            if ($similarity > 85) {
                return $id;
            }
        }
    }

    /**
     * @return int|null
     */
    protected function getRelatedUser()
    {
        // Get user email
        $extractor = new IdentityExtractor($this->command->from);
        $user = head($extractor->extract());
        $synchronizer = new UserSynchronizer($user);

        return $synchronizer->persist()->id;
    }

    /**
     * @param string $references
     *
     * @return int|null
     */
    protected function getCommentFromReference($references)
    {
        // Just get the last reference cause
        $references = explode('>', $references);
        $reference = last(array_filter($references));
        $reference = $reference ? trim($reference) : null;
        if (!$reference) {
            return;
        }

        // Try to retrieve the comment the reference's about
        try {
            $reference = $this->internals->findArticleFromReference($reference);
            $comment = $reference && isset($this->parsed[$reference]) ? $this->parsed[$reference] : null;
        } catch (InvalidArgumentException $exception) {
            return;
        }

        return $comment ?: null;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// COMMENT CREATION //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Create a comment from a NNTP article.
     *
     * @param int      $thread
     * @param int      $user
     * @param int|null $comment
     *
     * @return Comment
     */
    protected function createCommentFromArticle(int $thread, int $user, int $comment = null)
    {
        try {
            $contents = $this->internals->getArticleBody($this->command->number);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $datetime = $this->getDatetime();
        $synchronizer = new CommentSynchronizer(array_merge((array) $this->command, [
            'contents' => $contents,
            'comment_id' => $comment,
            'thread_id' => $thread,
            'user_id' => $user,
            'timestamps' => $datetime,
        ]));

        return $synchronizer->persist();
    }
}
