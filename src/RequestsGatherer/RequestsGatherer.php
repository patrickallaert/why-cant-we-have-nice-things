<?php
namespace History\RequestsGatherer;

use History\Entities\Models\Question;
use History\Entities\Models\Request;
use History\Entities\Models\Vote;
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
        $extractor = new InformationsExtractor($crawler);

        $informations = $extractor->getRequestInformations();
        if (!$informations['name']) {
            return;
        }

        // Retrieve or create the request
        $request = Request::firstOrNew(['link' => $link]);

        // Update timestamps
        $request->name       = $informations['name'];
        $request->condition  = $informations['condition'];
        $request->created_at = $informations['timestamp'];
        $request->updated_at = $informations['timestamp'];
        $request->save();

        foreach ($informations['questions'] as $question) {
            $votes = $question['votes'];
            $question = Question::firstOrCreate([
                'name'       => $question['name'],
                'request_id' => $request->id,
            ]);

            // Insert question votes
            $votes = array_map(function ($vote) use ($question) {
                $vote['question_id'] = $question->id;

                return $vote;
            }, $votes);

            $question->votes()->delete();
            Vote::insert($votes);
        }
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
