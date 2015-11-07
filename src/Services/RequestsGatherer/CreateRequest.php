<?php

namespace History\Services\RequestsGatherer;

use GuzzleHttp\Client;
use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Synchronizers\QuestionSynchronizer;
use History\Entities\Synchronizers\RequestSynchronizer;
use History\Entities\Synchronizers\UserSynchronizer;
use History\Entities\Synchronizers\VersionSynchronizer;
use History\Entities\Synchronizers\VoteSynchronizer;
use History\Services\RequestsGatherer\Extractors\RequestExtractor;
use History\Services\RequestsGatherer\Extractors\UserExtractor;
use History\Services\Threading\Job;
use Illuminate\Contracts\Cache\Repository;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

class CreateRequest extends Job
{
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var string
     */
    protected $request;

    /**
     * @param Repository $cache
     * @param string     $request
     */
    public function __construct(Repository $cache, $request)
    {
        $this->identifier = $request;
        $this->request    = $request;
        $this->cache      = $cache;
    }

    /**
     * Run the job.
     */
    public function fire(array $payload)
    {
        // Extract request informations
        $crawler      = $this->getPageCrawler($this->request);
        $extractor    = new RequestExtractor($crawler);
        $informations = $extractor->extract();
        if (!$informations['name']) {
            return;
        }

        // Retrieve or create the request
        // and update its informations
        $informations['link'] = $this->request;
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
        if (!$contents = $this->cache->tags('php')->get($link)) {
            $request  = (new Client())->request('GET', $link);
            $contents = $request->getBody()->getContents();
            $this->cache->tags('php')->forever($link, $contents);
        }

        return new Crawler($contents);
    }
}
