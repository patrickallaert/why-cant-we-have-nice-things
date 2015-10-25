<?php
namespace History\Services\Internals;

use DateTime;
use History\Collection;
use History\Entities\Models\Comment;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\EmailExtractor;
use Rvdv\Nntp\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class InternalsSynchronizer
{
    /**
     * @var integer
     */
    const ARTICLES = 5000;

    /**
     * @var Internals
     */
    protected $internals;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * InternalsSynchronizer constructor.
     *
     * @param Internals $internals
     */
    public function __construct(Internals $internals)
    {
        $this->internals = $internals;
        $this->output    = new NullOutput();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Synchronize the php.internals mailing list
     * to a fucking usable format
     */
    public function synchronize()
    {
        $existingComments = Comment::lists('xref')->all();

        $comments = new Collection();
        $articles = $this->internals->getLatestArticles(self::ARTICLES);
        $progress = new ProgressBar($this->output, self::ARTICLES);
        $progress->start();

        foreach ($articles as $article) {
            $progress->advance();

            // If we already synchronized this one, skip it
            if (in_array($article['xref'], $existingComments)) {
                continue;
            }

            // If the article is not about RFCs, fuck off
            if (strpos($article['subject'], 'RFC') === false) {
                continue;
            }

            // Get the RFC the message relates to
            $article['subject'] = $this->cleanupSubject($article['subject']);
            if (!$request = $this->getRelatedRequest($article)) {
                continue;
            }

            // Get the user that posted the message
            if (!$user = $this->getRelatedUser($article)) {
                continue;
            }

            // Grab the message contents and insert into database
            $comments[] = $this->createCommentFromArticle($article, $request, $user);
        }

        $progress->finish();

        return $comments;
    }

    /**
     * Remove tags from an article's subject
     *
     * @param string $subject
     *
     * @return string
     */
    protected function cleanupSubject($subject)
    {
        return trim(strtr($subject, [
            'Re:'          => null,
            '[RFC]'        => null,
            '[DISCUSSION]' => null,
            '[PHP-DEV]'    => null,
            '[VOTE]'       => null,
        ]));
    }

    /**
     * @param array $article
     *
     * @return integer|null
     */
    protected function getRelatedRequest(array $article)
    {
        $subject          = $article['subject'];
        $existingRequests = Request::lists('id', 'name');

        // Try to find the RFC the message's talking about
        $request = array_get($existingRequests, $subject);
        if (!$request) {
            return;
        }

        return $request;
    }

    /**
     * @param array $article
     *
     * @return integer|null
     */
    protected function getRelatedUser(array $article)
    {
        $existingUsers = User::lists('id', 'email');

        $extractor = new EmailExtractor($article['from']);
        $email     = head($extractor->extract());

        return array_get($existingUsers, $email);
    }

    /**
     * Create a comment from a NNTP article
     *
     * @param array   $article
     * @param integer $request
     * @param integer $user
     *
     * @return Comment
     */
    protected function createCommentFromArticle(array $article, $request, $user)
    {
        try {
            $contents = $this->internals->getArticleBody($article['number']);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $datetime = new DateTime($article['date']);

        // If we already synchronized this one, skip it
        $comment = Comment::firstOrNew(['xref' => $article['xref']]);
        if ($comment->exists) {
            return $comment;
        }

        // Else update attributes
        $comment->name       = $article['subject'];
        $comment->contents   = $contents;
        $comment->request_id = $request;
        $comment->user_id    = $user;
        $comment->created_at = $datetime;
        $comment->updated_at = $datetime;
        $comment->save();

        return $comment;
    }
}
