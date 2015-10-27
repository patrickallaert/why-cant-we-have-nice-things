<?php
namespace History\Services\RequestsGatherer;

use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Services\RequestsGatherer\Extractors\RequestExtractor;
use History\Services\RequestsGatherer\Extractors\RequestsExtractor;
use History\Services\RequestsGatherer\Extractors\UserExtractor;
use History\Services\RequestsGatherer\Synchronizers\QuestionSynchronizer;
use History\Services\RequestsGatherer\Synchronizers\RequestSynchronizer;
use History\Services\RequestsGatherer\Synchronizers\UserSynchronizer;
use History\Services\RequestsGatherer\Synchronizers\VoteSynchronizer;
use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class RequestsGatherer
{
    /**
     * @var string
     */
    const DOMAIN = 'https://wiki.php.net';

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache  = $cache;
        $this->output = new NullOutput();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// EXTRACTION /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get all the requests.
     */
    public function createRequests()
    {
        $crawler  = $this->getPageCrawler(static::DOMAIN.'/rfc');
        $requests = (new RequestsExtractor($crawler))->extract();

        $progress = new ProgressBar($this->output, count($requests));
        foreach ($requests as $request) {
            $this->createRequest(static::DOMAIN.$request);
            $progress->advance();
        }

        $progress->finish();
    }

    /**
     * Create a request from an RFC link.
     *
     * @param string $link
     */
    public function createRequest($link)
    {
        $crawler   = $this->getPageCrawler($link);
        $extractor = new RequestExtractor($crawler);

        $informations = $extractor->extract();
        if (!$informations['name']) {
            return;
        }

        // Retrieve or create the request
        // and update its informations
        $informations['link'] = $link;
        $synchronizer         = new RequestSynchronizer($informations);
        $request              = $synchronizer->persist();

        $this->createQuestions($request, $informations['questions']);
        $this->createAuthors($request, $informations['authors']);
    }

    /**
     * @param Request $request
     * @param array   $authors
     */
    protected function createAuthors(Request $request, array $authors)
    {
        foreach ($authors as $key => $author) {
            $authors[$key] = (new UserSynchronizer($author))->persist()->id;
        }

        $request->authors()->sync($authors);
    }

    /**
     * Create the questions for a request.
     *
     * @param Request $request
     * @param array   $questions
     */
    protected function createQuestions(Request $request, array $questions)
    {
        foreach ($questions as $informations) {
            $question = new QuestionSynchronizer($informations, $request);
            $question = $question->persist();

            // Sanitize vote structure
            $votes = $informations['votes'];
            foreach ($votes as $vote) {
                $user = $this->createUser($vote['user_id']);
                $vote = new VoteSynchronizer($vote, $question, $user);
                $vote->persist();
            }
        }
    }

    /**
     * Create an user from its username.
     *
     * @param string $username
     *
     * @return User
     */
    protected function createUser($username)
    {
        // If we already have an user with that username and
        // all his/her infos are filled in, just return it
        $existing = User::where('name', $username)->first();
        if ($existing && $existing->full_name && $existing->email) {
            return $existing;
        }

        // Else find his informations and extract them
        $crawler      = $this->getPageCrawler('http://people.php.net/'.$username);
        $extractor    = new UserExtractor($crawler);
        $synchronizer = new UserSynchronizer($extractor->extract());

        return $synchronizer->persist();
    }

    /**
     * @param string $link
     *
     * @return Crawler
     */
    protected function getPageCrawler($link)
    {
        $contents = $this->cache->rememberForever($link, function () use ($link) {
            return file_get_contents($link);
        });

        return new Crawler($contents);
    }
}
