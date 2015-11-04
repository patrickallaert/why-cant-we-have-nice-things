<?php

namespace History\Services\RequestsGatherer;

use History\Console\HistoryStyle;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Synchronizers\QuestionSynchronizer;
use History\Entities\Synchronizers\RequestSynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Entities\Synchronizers\VersionSynchronizer;
use History\Entities\Synchronizers\VoteSynchronizer;
use History\Services\RequestsGatherer\Extractors\RequestExtractor;
use History\Services\RequestsGatherer\Extractors\RequestsExtractor;
use History\Services\RequestsGatherer\Extractors\UserExtractor;
use Illuminate\Contracts\Cache\Repository;
use InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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
     * @var HistoryStyle
     */
    protected $output;

    /**
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache  = $cache;
        $this->output = new HistoryStyle(new ArrayInput([]), new NullOutput());
    }

    /**
     * @param HistoryStyle $output
     */
    public function setOutput(HistoryStyle $output)
    {
        $this->output = $output;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// EXTRACTION /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get all the requests.
     *
     * @return Request[]
     */
    public function createRequests()
    {
        $crawler = $this->getPageCrawler(static::DOMAIN.'/rfc');
        if (!$crawler) {
            return;
        }

        $created  = [];
        $requests = (new RequestsExtractor($crawler))->extract();
        $this->output->progressIterator($requests, function ($request) use (&$created) {
            $created[] = $this->createRequest(static::DOMAIN.$request);
        });

        return $created;
    }

    /**
     * Create a request from an RFC link.
     *
     * @param string $link
     *
     * @return Request
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

        /* @var Request $request */
        $this->createVersions($request, $informations['versions']);
        $this->createQuestions($request, $informations['questions']);
        $this->createAuthors($request, $informations['authors']);

        return $request;
    }

    /**
     * Create an RFC's versions.
     *
     * @param Request $request
     * @param array   $versions
     */
    public function createVersions(Request $request, array $versions)
    {
        foreach ($versions as $version) {
            $version['request_id'] = $request->id;
            $synchronizer          = new VersionSynchronizer($version);
            $synchronizer->persist();
        }
    }

    /**
     * @param Request $request
     * @param array   $authors
     */
    public function createAuthors(Request $request, array $authors)
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
    public function createQuestions(Request $request, array $questions)
    {
        foreach ($questions as $informations) {
            $question = new QuestionSynchronizer($informations, $request);
            $question = $question->persist();

            /* @var Question $question */
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
    public function createUser($username)
    {
        // If we already have an user with that username and
        // all his/her infos are filled in, just return it
        $existing = User::where('name', $username)->first();
        if ($existing && $existing->full_name && $existing->email) {
            return $existing;
        }

        try {
            // Else find his informations and extract them
            $crawler    = $this->getPageCrawler('http://people.php.net/'.$username);
            $extractor  = new UserExtractor($crawler);
            $attributes = array_filter($extractor->extract());
        } catch (InvalidArgumentException $exception) {
            $attributes = [];
        }

        // Merge attributes
        $attributes   = array_merge(['name' => $username], $attributes);
        $synchronizer = new UserSynchronizer($attributes);

        return $synchronizer->persist();
    }

    /**
     * @param string $link
     *
     * @return Crawler
     */
    protected function getPageCrawler($link)
    {
        $contents = $this->cache->tags('php')->rememberForever($link, function () use ($link) {
            return file_get_contents($link);
        });

        return new Crawler($contents);
    }
}
