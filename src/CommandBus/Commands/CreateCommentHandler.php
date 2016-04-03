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
use Illuminate\Support\Fluent;
use Rvdv\Nntp\Exception\InvalidArgumentException;

class CreateCommentHandler extends AbstractHandler
{
    /**
     * @var Fluent
     */
    protected $article;

    /**
     * @var Thread
     */
    protected $thread;

    /**
     * @var Internals
     */
    protected $internals;

    /**
     * @param Internals $internals
     */
    public function __construct(Internals $internals)
    {
        $this->internals = $internals;
    }

    /**
     * @param \History\CommandBus\Commands\CreateCommentCommand $command
     *
     * @return Comment|void
     */
    public function handle(CreateCommentCommand $command)
    {
        $this->internals->setGroup($command->group, true);
        $article = $this->internals->getArticle($command->articleNumber);
        $this->article = new Fluent($article);

        // Get the RFC the message relates to
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
     * @param int      $user
     * @param int|null $request
     *
     * @return Thread
     */
    protected function getParentThread(int $user, int $request = null): Thread
    {
        $thread = Thread::firstOrNew(['name' => $this->article->subject]);

        // Retrieve group
        list($group) = explode(':', $this->article->xref);
        $group = Group::firstOrCreate(['name' => $group]);

        // Add additional attributes if we just
        // created the thread
        if (!$thread->exists) {
            $thread->request_id = $request;
            $thread->user_id = $user;
            $thread->group_id = $group->id;
            $thread->created_at = $this->article->date;
            $thread->updated_at = $this->article->date;
            $thread->save();
        }

        return $thread;
    }

    /**
     * @return int|null
     */
    protected function getRelatedRequest()
    {
        $subject = $this->article->subject;
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
        $extractor = new IdentityExtractor($this->article->from);
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
        $synchronizer = new CommentSynchronizer(array_merge($this->article->toArray(), [
            'thread_id' => $thread,
            'user_id' => $user,
            'timestamps' => $this->article->date,
        ]));

        return $synchronizer->persist();
    }
}
