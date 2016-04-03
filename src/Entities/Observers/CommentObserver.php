<?php

namespace History\Entities\Observers;

use History\Entities\Models\Threads\Comment;

class CommentObserver
{
    /**
     * @param Comment $comment
     */
    public function created(Comment $comment)
    {
        $comment->registerEvent('comment_created');
    }
}
