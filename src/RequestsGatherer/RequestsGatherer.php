<?php
namespace History\RequestsGatherer;

use History\Entities\Request;
use History\Entities\User;
use Illuminate\Contracts\Cache\Repository;
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
     * Get all the requests
     */
    public function createRequests()
    {
        $crawler = $this->getPageCrawler(static::DOMAIN.'/rfc');
        $users   = [];

        return $crawler->filter('li.level1 a.wikilink1')->each(function ($request) use ($users) {
            $link           = static::DOMAIN.$request->attr('href');
            $requestCrawler = $this->getPageCrawler($link);
            $name           = $this->getRequestName($requestCrawler);
            if (!$name) {
                return;
            }

            $request = Request::firstOrCreate([
                'name' => $name,
                'link' => $link,
            ]);

            $this->saveRequestVotes($request, $requestCrawler);
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

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Extract the name of a request
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
        $title = str_replace('Request for Comments:', '', $title);

        return trim($title);
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
