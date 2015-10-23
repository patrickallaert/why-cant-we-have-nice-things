<?php
namespace History\RequestsGatherer;

use History\Entities\Request;
use History\Entities\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class RequestsGatherer
{
    /**
     * @type string
     */
    const DOMAIN = 'https://wiki.php.net/';

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * RequestsGatherer constructor.
     *
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the votes from all users
     *
     * @return Collection
     */
    public function getUserVotes()
    {
        // Gather users votes
        $users    = [];
        $requests = $this->createRequests();
        foreach ($requests as $request) {
            foreach ($request->votes as $vote) {
                $user = $vote['user'];
                if (!isset($users[$user])) {
                    $users[$user] = [];
                }

                $users[$user][$request->link] = $vote['voted'];
            }
        }

        // Compute totals
        $users = new Collection($users);
        foreach ($users as $user => $votes) {
            $votedYes   = count(array_filter($votes));
            $totalVotes = count($votes);

            $users[$user] = new User([
                'name'      => $user,
                'votes'     => $votes,
                'voted_yes' => $votedYes,
                'voted_no'  => $totalVotes - $votedYes,
                'approval'  => round($votedYes / $totalVotes, 3),
                'total'     => $totalVotes,
            ]);
        }

        return $users;
    }

    /**
     * Get all the requests
     */
    public function createRequests()
    {
        return $this->cache->rememberForever('requests-votes', function () {
            $crawler = $this->getPageCrawler(static::DOMAIN.'/rfc');
            $users   = [];

            return $crawler->filter('li.level1 a.wikilink1')->each(function ($request) use ($users) {
                $link           = static::DOMAIN.$request->attr('href');
                $requestCrawler = $this->getPageCrawler($link);

                $request = Request::firstOrCreate([
                    'name' => $requestCrawler->filter('h1')->text(),
                    'link' => $link,
                ]);

                $this->saveRequestVotes($request, $requestCrawler);

                return $request;
            });
        });
    }

    /**
     * @param Request $request
     * @param Crawler $crawler
     *
     * @return array
     */
    protected function saveRequestVotes(Request $request, Crawler $crawler)
    {
        return $crawler
            ->filter('table.inline tr')
            ->reduce(function ($vote) {
                return $vote->filter('td.rightalign a')->count() > 0;
            })->each(function ($vote) use ($request) {
                $user  = $vote->filter('td.rightalign a')->text();
                $voted = $vote->filter('td:nth-child(2) img')->count() ? true : false;

                // Create user
                $user = User::firstOrCreate([
                    'name' => $user,
                ]);

                // Save his vote on this request
                $request->votes()->create([
                    'user_id' => $user->id,
                    'vote'    => $voted,
                ]);
            });
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
