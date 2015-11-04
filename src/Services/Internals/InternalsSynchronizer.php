<?php
namespace History\Services\Internals;

use Carbon\Carbon;
use DateTime;
use Exception;
use History\Console\HistoryStyle;
use History\Entities\Models\Comment;
use History\Entities\Models\Request;
use History\Entities\Synchronizers\CommentSynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Services\IdentityExtractor;
use Rvdv\Nntp\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class InternalsSynchronizer
{
    /**
     * @var int
     */
    const FIRST_RFC = 40000;

    /**
     * @var int
     */
    const CHUNK = 500;

    /**
     * @var Internals
     */
    protected $internals;

    /**
     * @var HistoryStyle
     */
    protected $output;

    /**
     * The existing comments.
     *
     * @var array
     */
    protected $parsed;

    /**
     * The existing requests.
     *
     * @var array
     */
    protected $existingRequests;

    /**
     * The created comments.
     *
     * @var array
     */
    protected $created = [];

    /**
     * InternalsSynchronizer constructor.
     *
     * @param Internals $internals
     */
    public function __construct(Internals $internals)
    {
        $this->internals = $internals;
        $this->output    = new HistoryStyle(new ArrayInput([]), new NullOutput());
    }

    /**
     * @param HistoryStyle $output
     */
    public function setOutput(HistoryStyle $output)
    {
        $this->output = $output;
    }

    /**
     * Synchronize the php.internals mailing list
     * to a fucking usable format.
     */
    public function synchronize()
    {
        $this->parsed           = Comment::orderBy('xref', 'DESC')->lists('id', 'xref')->all();
        $this->existingRequests = Request::lists('id', 'name');

        // Start at the last comment we parsed
        $start = self::FIRST_RFC;
        $count = $this->internals->getTotalNumberArticles();
        $total = $count - $start;

        $progress = $this->output->createProgressBar($total);
        $format   = $progress->getFormatDefinition('very_verbose');
        $progress->setFormat("%message%\n".$format);
        $progress->setMessage('Getting messages');
        $progress->setRedrawFrequency(350);
        $progress->start();
        for ($i = $start; $i <= $count; $i += self::CHUNK) {
            $to = $i + (self::CHUNK - 1);

            // Process this chunk of articles
            $articles = $this->internals->getArticles($i, $to);
            foreach ($articles as $article) {
                if (!$article) {
                    break;
                }

                $progress->setMessage('Getting message '.$article['xref']);
                $this->processArticle($article);
                $progress->advance();
            }
        }

        $progress->finish();

        return array_values($this->created);
    }

    /**
     * Process a chunk of articles.
     *
     * @param array $article
     *
     * @return Comment|void
     */
    public function processArticle(array $article)
    {
        // If we already synchronized this one, skip it
        if (array_key_exists($article['xref'], $this->parsed)) {
            return;
        }

        // If the article is not about RFCs, fuck off
        if (!preg_match('/(RFC|VOTE)/i', $article['subject'])) {
            return;
        }

        // Get the RFC the message relates to
        $article['subject'] = $this->cleanupSubject($article['subject']);
        if (!$request = $this->getRelatedRequest($article)) {
            return;
        }

        // Get the user that posted the message
        $user = $this->getRelatedUser($article);

        // If the article has references, find them
        $comment = $this->getCommentFromReference($article['references']);

        // Grab the message contents and insert into database
        return $this->createCommentFromArticle($article, $request, $user, $comment);
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
            'RE'           => null,
            'Re:'          => null,
            'RFC'          => null,
            '[RFC]'        => null,
            '[DISCUSSION]' => null,
            '[PHP-DEV]'    => null,
            '[VOTE]'       => null,
        ]), ' :');
    }

    /**
     * @param array $article
     *
     * @return int|null
     */
    protected function getRelatedRequest(array $article)
    {
        $subject = $article['subject'];

        // Try to find an exact match
        if (array_key_exists($subject, $this->existingRequests)) {
            return $this->existingRequests[$subject];
        }

        // If the similarity between a message's title and an RFC name
        // is above 90%, consider a match
        foreach ($this->existingRequests as $name => $id) {
            similar_text(strtolower($subject), strtolower($name), $similarity);
            if ($similarity > 85) {
                return $id;
            }
        }
    }

    /**
     * @param array $article
     *
     * @return int|null
     */
    protected function getRelatedUser(array $article)
    {
        // Get user email
        $extractor    = new IdentityExtractor($article['from']);
        $user         = head($extractor->extract());
        $synchronizer = new UserSynchronizer($user);

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
        $references = explode('>', $references);
        $reference  = last(array_filter($references));
        $reference  = $reference ? trim($reference) : null;
        if (!$reference) {
            return;
        }

        // Try to retrieve the comment the reference's about
        try {
            $reference = $this->internals->findArticleFromReference($reference);
            $comment   = $reference && isset($this->parsed[$reference]) ? $this->parsed[$reference] : null;
        } catch (InvalidArgumentException $exception) {
            return;
        }

        return $comment ?: null;
    }

    /**
     * Create a comment from a NNTP article.
     *
     * @param array    $article
     * @param int      $request
     * @param int      $user
     * @param int|null $comment
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

        // Normalize date
        try {
            $datetime = $article['date'];
            $datetime = str_replace('(GMT Daylight Time)', '', $datetime);
            $datetime = new DateTime($datetime);
        } catch (Exception $exception) {
            $datetime = Carbon::now();
        }

        $synchronizer = new CommentSynchronizer(array_merge($article, [
            'contents'   => $contents,
            'comment_id' => $comment,
            'request_id' => $request,
            'user_id'    => $user,
            'timestamps' => $datetime,
        ]));

        // Save comment and append to existing
        /** @var Comment $comment */
        $comment                      = $synchronizer->persist();
        $this->created[$comment->id]  = $comment;
        $this->parsed[$comment->xref] = $comment->id;

        return $comment;
    }
}
