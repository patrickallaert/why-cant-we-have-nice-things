<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use DateTime;
use History\Entities\Models\Comment;
use History\Services\RequestsGatherer\AbstractModel;

class CommentSynchronizer extends AbstractSynchronizer
{
    /**
     * Synchronize an entity with our domain.
     *
     * @return AbstractModel
     */
    public function synchronize()
    {
        $datetime = new DateTime($this->get('date'));

        // If we already synchronized this one, skip it
        $comment = Comment::firstOrNew(['xref' => $this->get('xref')]);
        if ($comment->exists) {
            return $comment;
        }

        // Else update attributes
        $comment->name       = $this->get('subject');
        $comment->contents   = $this->get('contents');
        $comment->request_id = $this->get('request_id');
        $comment->user_id    = $this->get('user_id');
        $comment->created_at = $datetime;
        $comment->updated_at = $datetime;

        return $comment;
    }
}
