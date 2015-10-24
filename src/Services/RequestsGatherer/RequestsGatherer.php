<?php
namespace History\Services\RequestsGatherer;

use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use History\Services\RequestsGatherer\Extractors\RequestExtractor;
use History\Services\RequestsGatherer\Extractors\UserExtractor;
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
    const DOMAIN = 'https://wiki.php.net/';

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * RequestsGatherer constructor.
     *
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
        $requests = $crawler->filter('li.level1 a.wikilink1');

        $progress = new ProgressBar($this->output, $requests->count());
        $requests->each(function ($request) use ($progress) {
            $this->createRequest(static::DOMAIN.$request->attr('href'));
            $progress->advance();
        });

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
        $request             = Request::firstOrNew(['link' => $link]);
        $request->name       = $informations['name'];
        $request->condition  = $informations['condition'];
        $request->created_at = $informations['timestamp'];
        $request->updated_at = $informations['timestamp'];
        $request->save();

        $this->createQuestions($request, $informations['questions']);
    }

    /**
     * Create the questions for a request.
     *
     * @param Request $request
     * @param array   $questions
     */
    protected function createQuestions(Request $request, array $questions)
    {
        foreach ($questions as $question) {
            $votes    = $question['votes'];
            $question = Question::firstOrCreate([
                'name'       => $question['name'],
                'choices'    => $question['choices'],
                'request_id' => $request->id,
            ]);

            // Sanitize vote structure
            foreach ($votes as $key => $vote) {
                $vote['question_id'] = $question->id;
                $vote['user_id']     = $this->createUser($vote['user_id'])->id;
                $vote['updated_at']  = $vote['created_at'];

                $votes[$key] = $vote;
            }

            $question->votes()->delete();
            Vote::insert($votes);
        }
    }

    /**
     * Create an user if needed.
     *
     * @param string $username
     *
     * @return User
     */
    protected function createUser($username)
    {
        $crawler      = $this->getPageCrawler('http://people.php.net/'.$username);
        $extractor    = new UserExtractor($crawler);
        $informations = $extractor->extract();

        $user            = User::firstOrNew(['name' => $username]);
        $user->full_name = $informations['full_name'];
        $user->email     = $informations['email'];
        $user->save();

        return $user;
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
