<?php
namespace History\Services\Internals;

use DateTime;
use History\Entities\Models\Comment;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\EmailExtractor;
use Rvdv\Nntp\Exception\InvalidArgumentException;
use SplFixedArray;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class InternalsSynchronizer
{
    /**
     * @var integer
     */
    const CHUNK = 200;

    /**
     * @var Internals
     */
    protected $internals;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var array
     */
    protected $parsed;

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
        $this->parsed = Comment::lists('xref')->all();
        $count        = $this->internals->getTotalNumberArticles();

        $progress = new ProgressBar($this->output, $count / self::CHUNK);
        $progress->start();
        for ($i = 1; $i <= $count; $i += self::CHUNK) {
            $to = $i + (self::CHUNK - 1);

            // Process this chunk of articles
            $articles = $this->internals->getArticles($i, $to);
            $this->processArticles($articles);
            $progress->advance();
        }

        $progress->finish();
    }

    /**
     * Process a chunk of articles
     *
     * @param SplFixedArray $articles
     */
    public function processArticles(SplFixedArray $articles)
    {
        foreach ($articles as $article) {

            // If we already synchronized this one, skip it
            if (in_array($article['xref'], $this->parsed)) {
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
            $this->createCommentFromArticle($article, $request, $user);
        }
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////// INFORMATIONS EXTRACTORS //////////////////////
    //////////////////////////////////////////////////////////////////////

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
        $existingUsers = User::lists('id', 'email')->toArray();

        $extractor = new EmailExtractor($article['from']);
        $email     = head($extractor->extract());

        if ($existing = array_get($existingUsers, $email)) {
            return $existing;
        }

        return User::create(['email' => $email])->id;
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
            $contents = '';
            //$contents = $this->internals->getArticleBody($article['number']);
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
