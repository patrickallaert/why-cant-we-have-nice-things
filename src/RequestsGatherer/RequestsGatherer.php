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
        $requests = $this->getRequests();
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
    public function getRequests()
    {
        return $this->cache->rememberForever('requests-votes', function () {
            $crawler = $this->getPageCrawler(static::DOMAIN.'/rfc');
            $users   = [];

            return $crawler->filter('li.level1 a.wikilink1')->each(function ($request) use ($users) {
                $link           = static::DOMAIN.$request->attr('href');
                $requestCrawler = $this->getPageCrawler($link);

                return new Request([
                    'name'  => $requestCrawler->filter('h1')->text(),
                    'link'  => $link,
                    'votes' => $this->getRequestVotes($requestCrawler),
                ]);
            });
        });
    }

    /**
     * @param Crawler $crawler
     *
     * @return array
     */
    protected function getRequestVotes(Crawler $crawler)
    {
        return $crawler
            ->filter('table.inline tr')
            ->reduce(function ($vote) {
                return $vote->filter('td.rightalign a')->count() > 0;
            })->each(function ($vote) {
                $user  = $vote->filter('td.rightalign a')->text();
                $voted = $vote->filter('td:nth-child(2) img')->count() ? true : false;

                return [
                    'user'  => $user,
                    'voted' => $voted,
                ];
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
