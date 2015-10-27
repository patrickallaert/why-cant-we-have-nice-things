<?php
namespace History\Services\Internals;

use History\Entities\Models\Comment;
use History\Entities\Models\Request;
use History\Services\IdentityExtractor;
use History\Services\RequestsGatherer\Synchronizers\CommentSynchronizer;
use History\Services\RequestsGatherer\Synchronizers\UserSynchronizer;
use Illuminate\Support\Arr;
use Rvdv\Nntp\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class InternalsSynchronizer
{
    /**
     * @var int
     */
    const CHUNK = 500;

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
     * to a fucking usable format.
     */
    public function synchronize()
    {
        $this->parsed = Comment::lists('xref')->all();

        $count = $this->internals->getTotalNumberArticles();
        $start = 40000; // First RFC was #40037 so, can skip all these

        $progress = new ProgressBar($this->output, $count / self::CHUNK);
        $progress->start();
        for ($i = $start; $i <= $count; $i += self::CHUNK) {
            $to = $i + (self::CHUNK - 1);

            // Process this chunk of articles
            $articles = $this->internals->getArticles($i, $to);
            foreach ($articles as $article) {
                $this->processArticle($article);
            }
            $progress->advance();
        }

        $progress->finish();
    }

    /**
     * Process a chunk of articles.
     *
     * @param array $article
     */
    public function processArticle(array $article)
    {
        // If we already synchronized this one, skip it
        if (in_array($article['xref'], $this->parsed, true)) {
            return;
        }

        // If the article is not about RFCs, fuck off
        if (strpos($article['subject'], 'RFC') === false) {
            return;
        }

        // Get the RFC the message relates to
        $article['subject'] = $this->cleanupSubject($article['subject']);
        if (!$request = $this->getRelatedRequest($article)) {
            return;
        }

        // Get the user that posted the message
        if (!$user = $this->getRelatedUser($article)) {
            return;
        }

        // If the article has references, find them
        $comment = $this->getCommentFromReference($article['references']);

        // Grab the message contents and insert into database
        $this->createCommentFromArticle($article, $request, $user, $comment);
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
     * @return int|null
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
     * @return int|null
     */
    protected function getRelatedUser(array $article)
    {
        // Get user email
        $extractor = new IdentityExtractor($article['from']);
        $email     = head($extractor->extract())['email'];

        // Get user name
        preg_match_all('/\((.+)\)/', $article['from'], $matches);
        $name = Arr::get($matches, '1.0');

        $synchronizer = new UserSynchronizer([
            'email'     => $email,
            'full_name' => $name,
        ]);

        return $synchronizer->persist()->id;
    }

    /**
     * @param string $references
     *
     * @return int|null
     */
    private function getCommentFromReference($references)
    {
        // Just get the last reference cause
        $references = explode(' ', $references);
        $reference  = last($references);

        return;

        // Try to retrieve the comment the reference's about
        try {
            $reference = $this->internals->findArticleFromReference($reference);
            $comment   = Comment::where('xref', $reference)->first();
        } catch (InvalidArgumentException $exception) {
            return;
        }

        return $comment ? $comment->id : null;
    }

    /**
     * Create a comment from a NNTP article.
     *
     * @param array $article
     * @param int   $request
     * @param int   $user
     *
     * @return Comment
     */
    protected function createCommentFromArticle(array $article, $request, $user, $comment = null)
    {
        try {
            $contents = $this->internals->getArticleBody($article['number']);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $synchronizer = new CommentSynchronizer(array_merge($article, [
            'contents'   => $contents,
            'comment_id' => $comment,
            'request_id' => $request,
            'user_id'    => $user,
        ]));

        return $synchronizer->persist();
    }
}
