<?php
namespace History\Services\RequestsGatherer\Synchronizers;

use Carbon\Carbon;
use DateTime;
use Exception;
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
        try {
            $datetime = $this->get('date');
            $datetime = str_replace('(GMT Daylight Time)', '', $datetime);
            $datetime = new DateTime($datetime);
        } catch (Exception $exception) {
            $datetime = Carbon::now();
        }

        // If we already synchronized this one, skip it
        $comment = Comment::firstOrNew(['xref' => $this->get('xref')]);

        // Else update attributes
        $comment->name       = $this->get('subject');
        $comment->contents   = $this->get('contents');
        $comment->request_id = $this->get('request_id');
        $comment->comment_id = $comment->comment_id ?: $this->get('comment_id');
        $comment->user_id    = $this->get('user_id');
        $this->updateTimestamps($comment, $datetime);

        return $comment;
    }
}
