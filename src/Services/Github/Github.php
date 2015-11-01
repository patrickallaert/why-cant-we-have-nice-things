<?php
namespace History\Services\Github;

use Github\Client;
use History\Entities\Models\User;
use Illuminate\Contracts\Cache\Repository;

class Github
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * Github constructor.
     *
     * @param Repository $cache
     * @param Client     $client
     */
    public function __construct(Repository $cache, Client $client)
    {
        $this->client = $client;
        $this->cache  = $cache;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function searchUser(User $user)
    {
        // Search by email, then full name, then username
        $criterias = array_filter([$user->email, $user->full_name, $user->name]);
        foreach ($criterias as $criteria) {
            $results = $this->cache->rememberForever('github:search:'.$criteria, function () use ($criteria) {
                return $this->client->search()->users($criteria.' type:user');
            });

            if ($results['total_count']) {
                return $results;
            }
        }
    }

    /**
     * @param string $username
     *
     * @return array
     */
    public function getUserInformations($username)
    {
        return $this->cache->rememberForever('github:user:'.$username, function () use ($username) {
            return $this->client->user()->show($username);
        });
    }
}
