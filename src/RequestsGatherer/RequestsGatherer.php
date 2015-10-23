<?php
namespace History\RequestsGatherer;

use DateTime;
use History\Entities\Models\Request;
use History\Entities\Models\User;
use History\Entities\Models\Vote;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;
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
        $crawler = $this->getPageCrawler($link);
        $name    = $this->getRequestName($crawler);
        if (!$name) {
            return;
        }

        // Extract additional informations
        $votingConditions = $this->cleanWhitespace($this->getVotingConditions($crawler));
        $timestamp        = $this->getRequestTimestamp($crawler);

        $request = Request::firstOrNew([
            'name'       => $name,
            'condition'  => $votingConditions,
            'link'       => $link,
        ]);

        $request->created_at = $timestamp;
        $request->updated_at = $timestamp;
        $request->save();

        $this->saveRequestVotes($request, $crawler);
    }

    /**
     * @param \History\Entities\Models\Request $request
     * @param Crawler                          $crawler
     *
     * @return array
     */
    protected function saveRequestVotes(Request $request, Crawler $crawler)
    {
        $votes   = [];
        $choices = $crawler->filter('table tr.row1 td')->each(function ($choice) {
            return $choice->text();
        });

        $crawler
            ->filter('table.inline tr')
            ->reduce(function ($vote) {
                return $vote->filter('td.rightalign a')->count() > 0;
            })->each(function ($vote) use ($request, &$votes, $choices) {
                $user  = $vote->filter('td.rightalign a')->text();
                $voted = !$vote->filter('td:last-child img')->count();

                // Create user
                $user = User::firstOrCreate([
                    'name' => $user,
                ]);

                // Save vote for this request
                $votes[] = [
                    'request_id' => $request->id,
                    'user_id'    => $user->id,
                    'vote'       => $voted,
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                ];
            });

        // Purge and recreate votes
        $request->votes()->delete();
        Vote::insert($votes);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Extract the name of a request.
     *
     * @param Crawler $crawler
     *
     * @return string
     */
    protected function getRequestName(Crawler $crawler)
    {
        $title = $crawler->filter('h1');
        if (!$title->count()) {
            return;
        }

        // Remove some tags from title
        $title = $title->text();
        $title = str_replace('PHP RFC:', '', $title);
        $title = str_replace('RFC:', '', $title);
        $title = str_replace('Request for Comments:', '', $title);

        return trim($title);
    }

    /**
     * Get the creation/update date of a request
     *
     * @param Crawler $crawler
     *
     * @return DateTime
     */
    protected function getRequestTimestamp(Crawler $crawler)
    {
        $timestamp = null;
        $crawler->filter('.level1 li')->each(function ($information) use (&$timestamp) {
            $text = $information->text();
            if (strpos($text, 'Date') !== false) {
                $timestamp = str_replace('Date:', '', $text);
                $timestamp = trim($timestamp);
            }
        });

        return DateTime::createFromFormat('Y-m-d', $timestamp);
    }

    // Get voting conditions
    /**
     * @param $requestCrawler
     *
     * @return mixed
     */
    protected function getVotingConditions(Crawler $requestCrawler)
    {
        $condition = $requestCrawler->filter('#proposed_voting_choices + div');
        if ($condition->count()) {
            return $condition->text();
        }

        $condition = $requestCrawler->filter('#vote + div p');
        if ($condition->count()) {
            return $condition->text();
        }
    }

    /**
     * @param $information
     *
     * @return string
     */
    protected function cleanWhitespace($information)
    {
        $information = Str::ascii($information);
        $information = preg_replace("/\s+/", ' ', $information);
        $information = trim($information);

        return $information;
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
