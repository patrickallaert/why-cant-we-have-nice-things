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
        $contents = $this->removeQuotesFromContent($this->get('contents'));
        $datetime = new DateTime($this->get('date'));

        // If we already synchronized this one, skip it
        $comment = Comment::firstOrNew(['xref' => $this->get('xref')]);
        if ($comment->exists) {
            return $comment;
        }

        // Else update attributes
        $comment->name       = $this->get('subject');
        $comment->contents   = $contents;
        $comment->request_id = $this->get('request_id');
        $comment->user_id    = $this->get('user_id');
        $comment->created_at = $datetime;
        $comment->updated_at = $datetime;

        return $comment;
    }

    /**
     * Fixed the fucked-up goddamn format of these
     * mailing ass lists
     *
     * @param string $contents
     *
     * @return string
     */
    protected function removeQuotesFromContent($contents)
    {
        // Remove any mentions and shit
        $contents = preg_replace('/^.*Content-(Disposition|Transfer|Type).*$/m', '', $contents);

        // Fix weird characters, bad encoding issues
        // and all other kinds of marvelous joys
        $contents = preg_replace('/^--.*--$/m', '', $contents);
        //$contents = preg_replace('/^>.*$/m', '', $contents);
        //$contents = preg_replace('/On [\w\d\s,:\t\n\r<>.@\-+]+wrote:/', '', $contents);

        // Cleanup all the empty lines
        $contents = preg_replace('/[\n]{3,}/', "\n\n", $contents);

        return trim($contents);
    }
}
