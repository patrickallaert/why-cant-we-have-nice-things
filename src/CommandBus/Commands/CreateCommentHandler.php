<?php

namespace History\CommandBus\Commands;

use Carbon\Carbon;
use DateTime;
use Exception;
use History\Entities\Models\Request;
use History\Entities\Models\Threads\Comment;
use History\Entities\Models\Threads\Group;
use History\Entities\Models\Threads\Thread;
use History\Entities\Synchronizers\CommentSynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Services\IdentityExtractor;
use History\Services\Internals\Internals;

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
        $request = $this->getRelatedRequest();

        // Get the user that posted the message
        $user = $this->getRelatedUser();
        if (!$user) {
            return;
        }

        // Find the thread this belongs to
        $thread = $this->getParentThread($user, $request);

        // Grab the message contents and insert into database
        return $this->createCommentFromArticle($thread->id, $user);
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
        $timezones = [
            'Eastern Daylight Time' => 'EDT',
            'Eastern Standard Time' => 'EST',
            'MET DST' => 'MET',
        ];

        // Try to change timezone to one PHP understands
        $date = strtr($this->command->date, $timezones);
        $date = preg_replace('/(.+)\(.+\)$/', '$1', $date);

        try {
            $datetime = new DateTime($date);
        } catch (Exception $exception) {
            $datetime = Carbon::createFromDate(1970, 01, 01);
        }

        return $datetime;
    }

    /**
     * @param int      $user
     * @param int|null $request
     *
     * @return Thread
     */
    protected function getParentThread(int $user, int $request = null): Thread
    {
        $thread = Thread::firstOrNew(['name' => $this->command->subject]);
        $datetime = $this->getDatetime();

        // Retrieve group
        list($group) = explode(':', $this->command->xref);
        $group = Group::firstOrCreate(['name' => $group]);

        // Add additional attributes if we just
        // created the thread
        if (!$thread->exists) {
            $thread->request_id = $request;
            $thread->user_id = $user;
            $thread->group_id = $group->id;
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

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// COMMENT CREATION //////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Create a comment from a NNTP article.
     *
     * @param int $thread
     * @param int $user
     *
     * @return \History\Entities\Models\Threads\Comment
     */
    protected function createCommentFromArticle(int $thread, int $user)
    {
        $group = $this->command->group;
        $this->internals->setGroup($group);
        $contents = $this->internals->getArticleBody($this->command->number);

        $datetime = $this->getDatetime();
        $synchronizer = new CommentSynchronizer(array_merge((array) $this->command, [
            'contents' => $contents,
            'reference' => $this->command->reference,
            'thread_id' => $thread,
            'user_id' => $user,
            'timestamps' => $datetime,
        ]));

        return $synchronizer->persist();
    }
}
